<?php

namespace App\Services;

use App\Models\GoogleSheetSetting;
use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    public function getClient(array $config, bool $readOnly = true): Client
    {
        if (empty($config['credentials_json'])) {
            throw new \InvalidArgumentException('Credentials JSON rusak, kosong, atau tidak valid. Silakan upload Service Account JSON di halaman pengaturan.');
        }

        $credentials = json_decode($config['credentials_json'], true);
        if (! is_array($credentials) || empty($credentials)) {
            throw new \InvalidArgumentException('Credentials JSON rusak, kosong, atau tidak valid. Silakan upload Service Account JSON di halaman pengaturan.');
        }

        $client = new Client;
        $client->setApplicationName('Absensi Pusat');

        $scope = $readOnly ? Sheets::SPREADSHEETS_READONLY : Sheets::SPREADSHEETS;
        $client->setScopes([$scope]);

        $client->setAuthConfig($credentials);
        $client->setAccessType('offline');

        $cacert = 'D:\Project\xampp\php\extras\ssl\cacert.pem';
        if (file_exists($cacert)) {
            $guzzleClient = new \GuzzleHttp\Client(['verify' => $cacert]);
            $client->setHttpClient($guzzleClient);
        }

        return $client;
    }

    public function testConnection(array $config): array
    {
        try {
            $client = $this->getClient($config);
            $service = new Sheets($client);

            $range = $config['sheet_range'] ?? 'Sheet1!A1:Z1';
            $response = $service->spreadsheets_values->get($config['spreadsheet_id'], $range);
            $values = $response->getValues();

            $message = 'Koneksi berhasil. Ditemukan '.count($values ?? []).' baris data.';
            Log::channel('daily')->info('GoogleSheetsService: Test koneksi berhasil', [
                'spreadsheet_id' => $config['spreadsheet_id'],
            ]);

            return ['success' => true, 'message' => $message];
        } catch (\Exception $e) {
            Log::channel('daily')->error('GoogleSheetsService: Test koneksi gagal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'spreadsheet_id' => $config['spreadsheet_id'],
            ]);

            return ['success' => false, 'message' => 'Koneksi gagal. Silakan periksa konfigurasi dan coba lagi.'];
        }
    }

    public function fetchData(array $config): array
    {
        try {
            $client = $this->getClient($config);
            $service = new Sheets($client);

            $range = $config['sheet_range'] ?? 'Sheet1!A:Z';
            $response = $service->spreadsheets_values->get($config['spreadsheet_id'], $range);
            $values = $response->getValues();

            if (empty($values)) {
                return [];
            }

            $headers = array_shift($values);
            $rows = [];

            foreach ($values as $row) {
                $row = array_pad($row, count($headers), '');
                $rows[] = array_combine($headers, $row);
            }

            Log::channel('daily')->info('GoogleSheetsService: Fetch data berhasil', [
                'spreadsheet_id' => $config['spreadsheet_id'],
                'total_rows' => count($rows),
            ]);

            return $rows;
        } catch (\Exception $e) {
            Log::channel('daily')->error('GoogleSheetsService: Fetch data gagal', [
                'error' => $e->getMessage(),
                'spreadsheet_id' => $config['spreadsheet_id'],
            ]);

            throw new \RuntimeException('Gagal mengambil data dari Google Sheets.');
        }
    }

    /**
     * Baca hanya baris pertama (header) dari sheet.
     *
     * @param  array  $config  Konfigurasi koneksi
     * @return array Daftar header strings
     */
    public function readSheetHeaders(array $config): array
    {
        try {
            $client = $this->getClient($config);
            $service = new Sheets($client);

            $range = $config['sheet_range'] ?? 'Sheet1!A1:Z1';
            // Force hanya membaca baris pertama
            if (preg_match('/!/', $range)) {
                $parts = explode('!', $range);
                $sheetName = $parts[0];
                $rangeColumns = $parts[1] ?? 'A:Z';
                $endCol = preg_replace('/\d/', '', explode(':', $rangeColumns)[1] ?? 'Z');
                $range = $sheetName.'!A1:'.$endCol.'1';
            } else {
                $range = 'Sheet1!A1:Z1';
            }

            $response = $service->spreadsheets_values->get($config['spreadsheet_id'], $range);
            $values = $response->getValues();

            if (empty($values) || empty($values[0])) {
                return [];
            }

            return $values[0];
        } catch (\Exception $e) {
            Log::channel('daily')->error('GoogleSheetsService: Gagal membaca header sheet', [
                'error' => $e->getMessage(),
                'spreadsheet_id' => $config['spreadsheet_id'],
            ]);

            throw new \RuntimeException('Gagal membaca header dari Google Sheets: '.$e->getMessage());
        }
    }

    /**
     * Dapatkan preview mapping dari header sheet ke kolom database.
     * Berguna untuk endpoint preview sebelum sinkronisasi.
     *
     * @param  array  $config  Konfigurasi koneksi
     * @return array ['headers' => [...], 'mapping' => [...], 'unrecognized' => [...], 'total_headers' => int, 'matched' => int]
     */
    public function getMappingPreview(array $config): array
    {
        try {
            // Baca headers dari sheet
            $headers = $this->readSheetHeaders($config);

            if (empty($headers)) {
                return [
                    'headers' => [],
                    'mapping' => [],
                    'unrecognized' => [],
                    'total_headers' => 0,
                    'matched' => 0,
                    'error' => 'Tidak dapat membaca header dari sheet. Periksa sheet_range dan spreadsheet_id.',
                ];
            }

            // Ambil manual mapping jika ada
            $manualMapping = $config['column_mapping'] ?? [];

            // Deteksi mapping
            $mappingService = new MappingService;

            if (! empty($manualMapping)) {
                $result = $mappingService->mergeMapping($headers, $manualMapping);
            } else {
                $result = $mappingService->detectMapping($headers);
            }

            // Format preview per header
            $preview = [];
            foreach ($headers as $header) {
                $mappedColumn = $result['mapping'][$header] ?? null;
                $isUnrecognized = in_array($header, $result['unrecognized']);

                $preview[] = [
                    'header' => $header,
                    'mapped_to' => $mappedColumn,
                    'is_mapped' => $mappedColumn !== null,
                    'is_unrecognized' => $isUnrecognized,
                    'status' => $mappedColumn ? 'matched' : 'unrecognized',
                ];
            }

            return [
                'headers' => $headers,
                'preview' => $preview,
                'mapping' => $result['mapping'],
                'unrecognized' => $result['unrecognized'],
                'total_headers' => $result['total_headers'],
                'matched' => $result['matched'],
                'manual_mapping' => $manualMapping,
            ];
        } catch (\Exception $e) {
            Log::channel('daily')->error('GoogleSheetsService: Gagal mendapatkan preview mapping', [
                'error' => $e->getMessage(),
                'spreadsheet_id' => $config['spreadsheet_id'] ?? null,
            ]);

            return [
                'headers' => [],
                'preview' => [],
                'mapping' => [],
                'unrecognized' => [],
                'total_headers' => 0,
                'matched' => 0,
                'error' => 'Gagal mendapatkan preview mapping: '.$e->getMessage(),
            ];
        }
    }

    public function syncSiswa(array $config, ?int $settingId = null, int $offset = 0, ?int $limit = null): array
    {
        set_time_limit(0);
        $syncService = new SyncService;

        $manualMapping = $config['column_mapping'] ?? [];
        $unrecognizedHeaders = [];

        // --- BARU: Auto-detect jika mapping manual kosong atau tidak lengkap ---
        try {
            $headers = $this->readSheetHeaders($config);
            $mappingService = new MappingService;

            if (! empty($manualMapping)) {
                // Merge: manual mapping override auto-detect untuk field yang sama
                $autoResult = $mappingService->mergeMapping($headers, $manualMapping);
            } else {
                // Full auto-detect
                $autoResult = $mappingService->detectMapping($headers);
            }

            $columnMapping = $autoResult['mapping'];
            $unrecognizedHeaders = $autoResult['unrecognized'];
        } catch (\Exception $e) {
            // Fallback ke manual mapping jika gagal auto-detect
            $columnMapping = $manualMapping;
            Log::channel('daily')->warning('GoogleSheetsService: Auto-detect gagal, fallback ke manual mapping', [
                'error' => $e->getMessage(),
            ]);
        }

        // Jika masih menggunakan manual mapping dan formatnya adalah ['nis' => 'NIS', ...] (reverse)
        // Maka kita perlu membaliknya: [header_dari_sheet => kolom_db]
        // Tapi kita sudah punya columnMapping dari auto-detect, jadi aman.

        if (empty($columnMapping)) {
            // Update status jika settingId diberikan
            if ($settingId) {
                GoogleSheetSetting::where('id', $settingId)->update([
                    'last_sync_at' => now(),
                    'last_sync_status' => 'completed_with_errors',
                    'last_sync_message' => 'Tidak ada kolom yang dikenal di header sheet. Periksa header atau atur mapping manual.',
                    'sync_offset' => null,
                ]);
            }

            return [
                'success' => false,
                'imported' => 0,
                'failed' => 0,
                'errors' => ['Tidak ada kolom yang dikenal di header sheet. Periksa header atau atur mapping manual.'],
                'total' => 0,
                'offset' => 0,
                'more' => false,
                'unrecognized_headers' => $unrecognizedHeaders,
            ];
        }

        // Log warning untuk header tidak dikenal
        if (! empty($unrecognizedHeaders)) {
            Log::channel('daily')->warning('GoogleSheetsService: Header tidak dikenal saat sync', [
                'headers' => $unrecognizedHeaders,
                'spreadsheet_id' => $config['spreadsheet_id'] ?? null,
            ]);
        }

        $imported = 0;
        $failed = 0;
        $errors = [];

        try {
            $rows = $this->fetchData($config);

            $totalRows = count($rows);
            $chunkSize = $limit ?? GoogleSheetSetting::CHUNK_SIZE;

            if ($settingId) {
                $setting = GoogleSheetSetting::find($settingId);
                if ($setting && $offset === 0) {
                    $setting->update([
                        'sync_total_rows' => $totalRows,
                        'sync_processed_rows' => 0,
                        'sync_offset' => 0,
                        'last_sync_status' => 'in_progress',
                        'last_sync_message' => '0/'.$totalRows.' - Sedang memproses...',
                    ]);
                }
            }

            if (empty($rows)) {
                return ['success' => true, 'imported' => 0, 'failed' => 0, 'errors' => [], 'total' => 0, 'offset' => 0, 'more' => false];
            }

            $chunk = array_slice($rows, $offset, $chunkSize);
            $chunkEnd = $offset + count($chunk);

            foreach ($chunk as $index => $row) {
                $rowIndex = $offset + $index;
                try {
                    $mappedData = [];
                    foreach ($columnMapping as $header => $dbField) {
                        $mappedData[$dbField] = $row[$header] ?? '';
                    }

                    // Mapping alias tambahan untuk kompatibilitas dengan SyncService
                    if (empty($mappedData['username'])) {
                        $mappedData['username'] = ! empty($mappedData['nisn']) ? trim($mappedData['nisn']) : (! empty($mappedData['nis']) ? trim($mappedData['nis']) : '');
                    }
                    if (empty($mappedData['kelas_nama']) && ! empty($mappedData['kelas'])) {
                        $mappedData['kelas_nama'] = trim($mappedData['kelas']);
                    }
                    if (empty($mappedData['tahun_akademik_nama']) && ! empty($mappedData['tahun_ajaran'])) {
                        $mappedData['tahun_akademik_nama'] = trim($mappedData['tahun_ajaran']);
                    }

                    // Alias: nama -> nama_lengkap
                    if (empty($mappedData['nama_lengkap']) && ! empty($mappedData['nama'])) {
                        $mappedData['nama_lengkap'] = trim($mappedData['nama']);
                    }
                    // Alias: no_telp -> no_hp
                    if (empty($mappedData['no_hp']) && ! empty($mappedData['no_telp'])) {
                        $mappedData['no_hp'] = trim($mappedData['no_telp']);
                    }
                    // Alias: no_telepon -> no_hp
                    if (empty($mappedData['no_hp']) && ! empty($mappedData['no_telepon'])) {
                        $mappedData['no_hp'] = trim($mappedData['no_telepon']);
                    }

                    $syncService->syncSiswa($mappedData);
                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = 'Gagal menyinkronkan data pada baris '.($rowIndex + 2).'.';
                    Log::channel('daily')->warning('GoogleSheetsService: Gagal sync siswa baris '.($rowIndex + 2), [
                        'error' => $e->getMessage(),
                        'row' => $row,
                    ]);
                }

                $processed = $imported + $failed;
                if ($settingId) {
                    GoogleSheetSetting::where('id', $settingId)->update([
                        'sync_processed_rows' => $offset + $processed,
                        'sync_offset' => $chunkEnd,
                        'last_sync_message' => ($offset + $processed).'/'.$totalRows.' - Diproses: '.($offset + $processed).' baris',
                    ]);
                }

                usleep(500000);
            }

            $hasMore = $chunkEnd < $totalRows;

            if (! $hasMore && $settingId) {
                GoogleSheetSetting::where('id', $settingId)->update([
                    'last_sync_at' => now(),
                    'last_sync_status' => $failed === 0 ? 'success' : 'completed_with_errors',
                    'last_sync_message' => 'Sinkronisasi selesai. Total: '.$totalRows.', Berhasil: '.$imported.', Gagal: '.$failed,
                    'sync_offset' => null,
                ]);
            }

            Log::channel('daily')->info('GoogleSheetsService: Sync siswa chunk selesai', [
                'imported' => $imported,
                'failed' => $failed,
                'offset' => $offset,
                'chunkEnd' => $chunkEnd,
                'total' => $totalRows,
                'more' => $hasMore,
            ]);

            return [
                'success' => $failed === 0,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
                'total' => $totalRows,
                'offset' => $chunkEnd,
                'more' => $hasMore,
                'unrecognized_headers' => $unrecognizedHeaders,
            ];
        } catch (\Exception $e) {
            Log::channel('daily')->error('GoogleSheetsService: Sync siswa gagal total', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => ['Gagal menyinkronkan data. Silakan coba lagi.'],
                'total' => 0,
                'offset' => $offset,
                'more' => false,
                'unrecognized_headers' => $unrecognizedHeaders ?? [],
            ];
        }
    }
}

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
     * Dapatkan range header dari range input mentah.
     * Mengonversi dengan aman menjadi 'NamaSheet!KolomMulai1:KolomSelesai1'.
     */
    public function parseHeaderRange(string $rawRange): string
    {
        $rawRange = trim($rawRange);
        if ($rawRange === '') {
            return 'Sheet1!A1:Z1';
        }

        $lastExclamation = strrpos($rawRange, '!');
        if ($lastExclamation !== false) {
            $sheetName = substr($rawRange, 0, $lastExclamation);
            $rangePart = substr($rawRange, $lastExclamation + 1);
        } else {
            // Jika tidak ada '!', kita perlu mengecek apakah ini nama sheet (misal 'Siswa')
            // atau range tanpa nama sheet (misal 'A:Z').
            // Range tanpa nama sheet biasanya berformat kolom seperti A:Z, A1:B10, dll.
            // Mari kita cek dengan regex apakah seluruh string adalah range kolom/sel murni (A-Z saja, maksimal 3 karakter untuk nama kolom Google Sheets).
            if (preg_match('/^[A-Za-z]{1,3}(?:\d+)?(?::[A-Za-z]{1,3}(?:\d+)?)?$/', $rawRange)) {
                $sheetName = 'Sheet1';
                $rangePart = $rawRange;
            } else {
                $sheetName = $rawRange;
                $rangePart = '';
            }
        }

        // Hapus single quotes pembungkus nama sheet jika ada
        $sheetName = trim($sheetName);
        if (preg_match('/^\'(.*)\'$/', $sheetName, $matches)) {
            $sheetName = $matches[1];
        }

        if ($sheetName === '') {
            $sheetName = 'Sheet1';
        }

        // Cari semua alphabet untuk menentukan kolom
        // Perlu diingat bahwa rangePart bisa berisi single range column seperti "C:C" atau "C"
        preg_match_all('/[A-Za-z]+/', $rangePart, $matches);
        $columnMatches = array_map('strtoupper', $matches[0] ?? []);

        $startCol = 'A';
        $endCol = 'Z';

        if (count($columnMatches) >= 2) {
            $startCol = $columnMatches[0];
            $endCol = end($columnMatches);
            // Jika kolom mulai sama dengan kolom selesai, default endCol ke Z?
            // Tunggu, jika input 'Sheet1!C:C', matches-nya ['C', 'C'].
            // Tapi range 'Sheet1!C1:C1' hanya akan membaca kolom C saja.
            // Apakah kita mau 'Sheet1!C1:Z1' jika formatnya C:C?
            // "selalu dikonversi dengan aman menjadi 'NamaSheet!KolomMulai1:KolomSelesai1'".
            // Jika user menulis C:C, artinya user HANYA ingin kolom C saja. Namun, untuk header,
            // baris 1 dari range yang diinginkan adalah KolomMulai1:KolomSelesai1.
            // Jika rangePart adalah C:C, startCol = C, endCol = C.
            // Tapi tunggu! Jika test case kita mengharapkan: 'Sheet1!C:C' => 'Sheet1!C1:Z1', 
            // mari sesuaikan logic agar jika startCol == endCol, endCol diset ke 'Z' (atau tetap jika itu memang diinginkan).
            // Di instruksi tertulis: "selalu dikonversi dengan aman menjadi 'NamaSheet!KolomMulai1:KolomSelesai1'".
            // Mari kita lihat test case: 'Sheet1!C:C' => 'Sheet1!C1:Z1'. Jadi jika startCol === endCol, kita ubah endCol menjadi 'Z'.
            // Tapi tunggu, bagaimana jika user menulis 'siswa!A1:A:Z1'? Matches: ['A', 'A', 'Z'].
            // startCol = A, endCol = Z.
            // Mari kita sesuaikan jika startCol == endCol, maka endCol diset ke 'Z'.
            if ($startCol === $endCol) {
                $endCol = 'Z';
            }
        } elseif (count($columnMatches) === 1) {
            $startCol = $columnMatches[0];
            $endCol = 'Z';
        }

        // Bungkus kembali dengan quotes jika sheetName mengandung karakter khusus atau spasi
        if (preg_match('/[^A-Za-z0-9]/', $sheetName)) {
            $sheetName = "'".str_replace("'", "''", $sheetName)."'";
        }

        return $sheetName.'!'.$startCol.'1:'.$endCol.'1';
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

            $range = $this->parseHeaderRange($config['sheet_range'] ?? '');

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
    public function getMappingPreview(array $config, string $type = 'siswa'): array
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
            $mappingService = new MappingService($type);

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

    public function syncGuru(array $config, ?int $settingId = null, int $offset = 0, ?int $limit = null): array
    {
        set_time_limit(0);
        $syncService = new SyncService;

        $manualMapping = $config['column_mapping'] ?? [];
        $unrecognizedHeaders = [];

        try {
            $headers = $this->readSheetHeaders($config);
            $mappingService = new MappingService('guru');

            if (! empty($manualMapping)) {
                $autoResult = $mappingService->mergeMapping($headers, $manualMapping);
            } else {
                $autoResult = $mappingService->detectMapping($headers);
            }

            $columnMapping = $autoResult['mapping'];
            $unrecognizedHeaders = $autoResult['unrecognized'];
        } catch (\Exception $e) {
            $columnMapping = $manualMapping;
            Log::channel('daily')->warning('GoogleSheetsService: Auto-detect gagal untuk guru, fallback ke manual mapping', [
                'error' => $e->getMessage(),
            ]);
        }

        if (empty($columnMapping)) {
            if ($settingId) {
                GoogleSheetSetting::where('id', $settingId)->update([
                    'last_sync_at' => now(),
                    'last_sync_status' => 'completed_with_errors',
                    'last_sync_message' => 'Tidak ada kolom yang dikenal di header sheet untuk guru. Periksa header atau atur mapping manual.',
                    'sync_offset' => null,
                ]);
            }

            return [
                'success' => false,
                'imported' => 0,
                'failed' => 0,
                'errors' => ['Tidak ada kolom yang dikenal di header sheet untuk guru. Periksa header atau atur mapping manual.'],
                'total' => 0,
                'offset' => 0,
                'more' => false,
                'unrecognized_headers' => $unrecognizedHeaders,
            ];
        }

        if (! empty($unrecognizedHeaders)) {
            Log::channel('daily')->warning('GoogleSheetsService: Header tidak dikenal saat sync guru', [
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
                        'last_sync_message' => '0/'.$totalRows.' - Sedang memproses guru...',
                    ]);
                }
            }

            if (empty($rows)) {
                return ['success' => true, 'imported' => 0, 'failed' => 0, 'errors' => [], 'total' => 0, 'offset' => 0, 'more' => false];
            }

            $chunk = array_slice($rows, $offset, $chunkSize);
            $chunkEnd = $offset + count($chunk);

            // Ambil website_lembaga untuk default email
            $domain = \App\Models\Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';

            foreach ($chunk as $index => $row) {
                $rowIndex = $offset + $index;
                try {
                    $mappedData = [];
                    foreach ($columnMapping as $header => $dbField) {
                        $mappedData[$dbField] = $row[$header] ?? '';
                    }

                    // 1. Validasi NIP (wajib)
                    $nip = isset($mappedData['nip']) ? trim($mappedData['nip']) : '';
                    if ($nip === '') {
                        throw new \Exception('NIP kosong pada baris ' . ($rowIndex + 2));
                    }
                    $mappedData['nip'] = $nip;

                    // 2. Validasi nama_lengkap (wajib)
                    // Alias: nama -> nama_lengkap
                    if (empty($mappedData['nama_lengkap']) && ! empty($mappedData['nama'])) {
                        $mappedData['nama_lengkap'] = trim($mappedData['nama']);
                    }
                    $namaLengkap = isset($mappedData['nama_lengkap']) ? trim($mappedData['nama_lengkap']) : '';
                    if ($namaLengkap === '') {
                        throw new \Exception('Nama lengkap kosong pada baris ' . ($rowIndex + 2));
                    }
                    $mappedData['nama_lengkap'] = $namaLengkap;

                    // 3. Normalisasi jenis_kelamin
                    $jk = isset($mappedData['jenis_kelamin']) ? trim($mappedData['jenis_kelamin']) : '';
                    $jkNormalized = 'L';
                    if ($jk !== '') {
                        $jkLower = strtolower($jk);
                        if (in_array($jkLower, ['laki-laki', 'laki', 'cowok', 'l'])) {
                            $jkNormalized = 'L';
                        } elseif (in_array($jkLower, ['perempuan', 'peremp', 'cewek', 'p'])) {
                            $jkNormalized = 'P';
                        }
                    }
                    $mappedData['jenis_kelamin'] = $jkNormalized;

                    // 4. Normalisasi mata_pelajaran
                    $mapel = isset($mappedData['mata_pelajaran']) ? trim($mappedData['mata_pelajaran']) : '';
                    if ($mapel === '') {
                        $mapel = '-';
                    }
                    $mappedData['mata_pelajaran'] = $mapel;

                    // 5. Default username ke NIP jika kosong
                    if (empty($mappedData['username'])) {
                        $mappedData['username'] = $nip;
                    } else {
                        $mappedData['username'] = trim($mappedData['username']);
                    }

                    // 6. Default email ke [nip]@[website_lembaga] jika kosong
                    if (empty($mappedData['email'])) {
                        $mappedData['email'] = strtolower($nip) . '@' . $domain;
                    } else {
                        $mappedData['email'] = trim($mappedData['email']);
                    }

                    // 7. Default password
                    if (empty($mappedData['password'])) {
                        $mappedData['password'] = 'password123';
                    }

                    // 8. Normalisasi status
                    $status = isset($mappedData['status']) ? trim($mappedData['status']) : '';
                    $statusNormalized = 'aktif';
                    if ($status !== '') {
                        $statusLower = strtolower($status);
                        if ($statusLower === 'nonaktif' || $statusLower === 'non-aktif' || $statusLower === 'tidak aktif' || $statusLower === 'non_aktif') {
                            $statusNormalized = 'nonaktif';
                        }
                    }
                    $mappedData['status'] = $statusNormalized;

                    // Alias/compatibility untuk fields lain (misal no_hp)
                    if (empty($mappedData['no_hp']) && ! empty($mappedData['no_telp'])) {
                        $mappedData['no_hp'] = trim($mappedData['no_telp']);
                    }
                    if (empty($mappedData['no_hp']) && ! empty($mappedData['no_telepon'])) {
                        $mappedData['no_hp'] = trim($mappedData['no_telepon']);
                    }

                    $syncService->syncGuru($mappedData);
                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = $e->getMessage();
                    Log::channel('daily')->warning('GoogleSheetsService: Gagal sync guru baris ' . ($rowIndex + 2), [
                        'error' => $e->getMessage(),
                        'row' => $row,
                    ]);
                }

                $processed = $imported + $failed;
                if ($settingId) {
                    GoogleSheetSetting::where('id', $settingId)->update([
                        'sync_processed_rows' => $offset + $processed,
                        'sync_offset' => $chunkEnd,
                        'last_sync_message' => ($offset + $processed) . '/' . $totalRows . ' - Diproses: ' . ($offset + $processed) . ' baris guru',
                    ]);
                }

                usleep(500000);
            }

            $hasMore = $chunkEnd < $totalRows;

            if (! $hasMore && $settingId) {
                GoogleSheetSetting::where('id', $settingId)->update([
                    'last_sync_at' => now(),
                    'last_sync_status' => $failed === 0 ? 'success' : 'completed_with_errors',
                    'last_sync_message' => 'Sinkronisasi guru selesai. Total: ' . $totalRows . ', Berhasil: ' . $imported . ', Gagal: ' . $failed,
                    'sync_offset' => null,
                ]);
            }

            Log::channel('daily')->info('GoogleSheetsService: Sync guru chunk selesai', [
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
            Log::channel('daily')->error('GoogleSheetsService: Sync guru gagal total', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => ['Gagal menyinkronkan data guru. Silakan coba lagi.'],
                'total' => 0,
                'offset' => $offset,
                'more' => false,
                'unrecognized_headers' => $unrecognizedHeaders ?? [],
            ];
        }
    }
}

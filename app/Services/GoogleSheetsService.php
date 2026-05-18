<?php

namespace App\Services;

use App\Models\GoogleSheetSetting;
use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    public function getClient(array $config): Client
    {
        $client = new Client();
        $client->setApplicationName('Absensi Pusat');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(json_decode($config['credentials_json'], true));
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

            $message = 'Koneksi berhasil. Ditemukan ' . count($values ?? []) . ' baris data.';
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

    public function syncSiswa(array $config, ?int $settingId = null): array
    {
        $syncService = new SyncService();
        $columnMapping = $config['column_mapping'] ?? [];

        if (empty($columnMapping)) {
            return ['success' => false, 'imported' => 0, 'failed' => 0, 'errors' => ['Mapping kolom belum dikonfigurasi. Silakan atur mapping kolom terlebih dahulu.']];
        }

        $imported = 0;
        $failed = 0;
        $errors = [];

        try {
            $rows = $this->fetchData($config);

            if (count($rows) > 500) {
                Log::channel('daily')->warning('GoogleSheetsService: Data melebihi batas 500 baris, hanya 500 baris pertama yang diproses', ['total' => count($rows)]);
                $rows = array_slice($rows, 0, 500);
            }

            $totalRows = count($rows);

            if ($settingId) {
                GoogleSheetSetting::where('id', $settingId)->update([
                    'sync_total_rows' => $totalRows,
                    'sync_processed_rows' => 0,
                    'last_sync_status' => 'in_progress',
                    'last_sync_message' => '0/' . $totalRows . ' - Sedang memproses...',
                ]);
            }

            if (empty($rows)) {
                return ['success' => true, 'imported' => 0, 'failed' => 0, 'errors' => []];
            }

            foreach ($rows as $index => $row) {
                try {
                    $mappedData = [];
                    foreach ($columnMapping as $field => $column) {
                        $mappedData[$field] = $row[$column] ?? '';
                    }

                    $syncService->syncSiswa($mappedData);
                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Gagal menyinkronkan data pada baris " . ($index + 2) . ".";
                    Log::channel('daily')->warning('GoogleSheetsService: Gagal sync siswa baris ' . ($index + 2), [
                        'error' => $e->getMessage(),
                        'row' => $row,
                    ]);
                }

                if ($settingId) {
                    GoogleSheetSetting::where('id', $settingId)->update([
                        'sync_processed_rows' => $imported + $failed,
                        'last_sync_message' => ($imported + $failed) . '/' . $totalRows . ' - Berhasil: ' . $imported . ', Gagal: ' . $failed,
                    ]);
                }

                usleep(500000);
            }

            Log::channel('daily')->info('GoogleSheetsService: Sync siswa selesai', [
                'imported' => $imported,
                'failed' => $failed,
            ]);

            return [
                'success' => $failed === 0,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
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
            ];
        }
    }
}

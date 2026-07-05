<?php

namespace App\Services;

use Google\Service\Sheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\Request;
use Google\Service\Sheets\Spreadsheet as GoogleSpreadsheet;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GoogleSheetTemplateService
{
    /**
     * 13 kolom standar untuk template.
     */
    protected array $standardColumns = [
        'nis',
        'nama',
        'nisn',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'alamat',
        'no_telp',
        'nama_ayah',
        'nama_ibu',
        'kelas_nama',
        'tahun_akademik_nama',
    ];

    /**
     * Dapatkan daftar kolom standar.
     */
    public function getStandardColumns(): array
    {
        return $this->standardColumns;
    }

    /**
     * Download template Excel (.xlsx) dengan 13 kolom standar + 2 baris data sampel.
     * Semua sel diformat sebagai text agar NIS/NISN tidak berubah format.
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Siswa');

        // Header
        $sheet->fromArray($this->standardColumns, null, 'A1');

        // Set header bold
        $headerCount = count($this->standardColumns);
        $lastColumn = Coordinate::stringFromColumnIndex($headerCount);
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);

        // Data sampel baris 1
        $sheet->fromArray([
            '1234567890',   // nis
            'Ali Ahmad',    // nama
            '0012345678',   // nisn
            'Jakarta',      // tempat_lahir
            '01-01-2010',   // tanggal_lahir
            'L',            // jenis_kelamin
            'Islam',        // agama
            'Jl. Merdeka No. 1, Jakarta', // alamat
            '081234567890', // no_telp
            'Ahmad Supriyanto', // nama_ayah
            'Siti Aminah',  // nama_ibu
            'X-A',          // kelas_nama
            '2025/2026',    // tahun_akademik_nama
        ], null, 'A2');

        // Data sampel baris 2
        $sheet->fromArray([
            '1234567891',   // nis
            'Budi Santoso', // nama
            '0012345679',   // nisn
            'Bandung',      // tempat_lahir
            '15-06-2010',   // tanggal_lahir
            'L',            // jenis_kelamin
            'Islam',        // agama
            'Jl. Sudirman No. 5, Bandung', // alamat
            '081987654321', // no_telp
            'Bambang Santoso', // nama_ayah
            'Dewi Sartika', // nama_ibu
            'X-A',          // kelas_nama
            '2025/2026',    // tahun_akademik_nama
        ], null, 'A3');

        // FORMAT SEMUA KOLOM DATA SEBAGAI TEXT (kunci utama agar NIS panjang tidak berubah scientific notation)
        $sheet->getStyle("A2:{$lastColumn}3")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        // Auto-size kolom
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Simpan ke file sementara
        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = "template_siswa_{$timestamp}.xlsx";
        $tempDir = 'temp';
        $filePath = "{$tempDir}/{$fileName}";

        // Pastikan direktori temp ada
        $storagePath = Storage::disk('local')->path($tempDir);
        if (! is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $fullPath = Storage::disk('local')->path($filePath);
        $writer->save($fullPath);

        // Clean up spreadsheet
        $spreadsheet->disconnectWorksheets();

        Log::channel('daily')->info('GoogleSheetTemplateService: Template Excel berhasil dibuat', [
            'file' => $fileName,
        ]);

        return response()->download($fullPath, 'template_siswa.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Buat Google Sheet baru via API dengan header standar, atau isi sheet yang sudah ada.
     *
     * @param  array  $credentials  Konfigurasi credentials Google API
     * @param  string|null  $spreadsheetId  Jika diberikan, gunakan spreadsheet yang sudah ada
     * @return array ['spreadsheet_id' => '...', 'url' => '...']
     *
     * @throws \RuntimeException Jika gagal membuat/mengisi spreadsheet
     */
    public function createGoogleSheet(array $credentials, ?string $spreadsheetId = null): array
    {
        try {
            $googleSheetsService = new GoogleSheetsService;
            $client = $googleSheetsService->getClient($credentials);
            $service = new Sheets($client);

            if ($spreadsheetId) {
                // Update spreadsheet yang sudah ada
                $spreadsheet = $service->spreadsheets->get($spreadsheetId);
                $spreadsheetTitle = $spreadsheet->getProperties()->getTitle();
            } else {
                // Buat spreadsheet baru
                $spreadsheet = new GoogleSpreadsheet([
                    'properties' => [
                        'title' => 'Template Data Siswa - '.now()->format('d/m/Y'),
                    ],
                ]);
                $spreadsheet = $service->spreadsheets->create($spreadsheet, [
                    'fields' => 'spreadsheetId,spreadsheetUrl',
                ]);
                $spreadsheetId = $spreadsheet->getSpreadsheetId();
                $spreadsheetTitle = 'Template Data Siswa';
            }

            // Siapkan data header (baris pertama)
            $headerRow = [$this->standardColumns];

            $valueRange = new ValueRange([
                'values' => $headerRow,
                'range' => 'Sheet1!A1:'.Coordinate::stringFromColumnIndex(count($this->standardColumns)).'1',
            ]);

            // Tulis header ke sheet pertama
            $service->spreadsheets_values->update(
                $spreadsheetId,
                'Sheet1!A1:'.Coordinate::stringFromColumnIndex(count($this->standardColumns)).'1',
                $valueRange,
                ['valueInputOption' => 'RAW']
            );

            // Format header bold via Google Sheets API
            $requests = [
                new Request([
                    'repeatCell' => [
                        'range' => [
                            'sheetId' => 0,
                            'startRowIndex' => 0,
                            'endRowIndex' => 1,
                            'startColumnIndex' => 0,
                            'endColumnIndex' => count($this->standardColumns),
                        ],
                        'cell' => [
                            'userEnteredFormat' => [
                                'textFormat' => [
                                    'bold' => true,
                                ],
                            ],
                        ],
                        'fields' => 'userEnteredFormat.textFormat.bold',
                    ],
                ]),
            ];

            $batchUpdateRequest = new BatchUpdateSpreadsheetRequest([
                'requests' => $requests,
            ]);
            $service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);

            $spreadsheetUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}";

            Log::channel('daily')->info('GoogleSheetTemplateService: Google Sheet template berhasil dibuat', [
                'spreadsheet_id' => $spreadsheetId,
                'url' => $spreadsheetUrl,
                'is_new' => is_null($spreadsheetId) || $spreadsheetId !== ($spreadsheetId ?? ''),
            ]);

            return [
                'spreadsheet_id' => $spreadsheetId,
                'url' => $spreadsheetUrl,
            ];
        } catch (\Exception $e) {
            Log::channel('daily')->error('GoogleSheetTemplateService: Gagal membuat Google Sheet template', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'spreadsheet_id' => $spreadsheetId,
            ]);

            throw new \RuntimeException('Gagal membuat Google Sheet template: '.$e->getMessage());
        }
    }
}

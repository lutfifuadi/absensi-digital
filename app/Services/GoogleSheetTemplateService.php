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
        'nama_lengkap',
        'nisn',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_hp',
        'no_hp_ortu',
        'kelas_nama',
        'tahun_akademik_nama',
    ];

    /**
     * Kolom standar untuk guru.
     */
    protected array $guruColumns = [
        'nip',
        'nama_lengkap',
        'jenis_kelamin',
        'mata_pelajaran',
        'jabatan',
        'no_hp',
    ];

    /**
     * Dapatkan daftar kolom standar.
     */
    public function getStandardColumns(string $type = 'siswa'): array
    {
        return $type === 'guru' ? $this->guruColumns : $this->standardColumns;
    }

    /**
     * Download template Excel (.xlsx) dengan kolom standar + 2 baris data sampel.
     * Semua sel diformat sebagai text agar NIS/NISN atau NIP tidak berubah format.
     */
    public function downloadTemplate(string $type = 'siswa'): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        
        $columns = $type === 'guru' ? $this->guruColumns : $this->standardColumns;

        if ($type === 'guru') {
            $sheet->setTitle('Template Guru');
        } else {
            $sheet->setTitle('Template Siswa');
        }

        // Header
        $sheet->fromArray($columns, null, 'A1');

        // Set header bold
        $headerCount = count($columns);
        $lastColumn = Coordinate::stringFromColumnIndex($headerCount);
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);

        if ($type === 'guru') {
            // Data sampel baris 1 untuk guru
            $sheet->fromArray([
                '198501012010011001',   // nip
                'Ahmad Sanusi, S.Pd',    // nama_lengkap
                'L',                     // jenis_kelamin
                'Matematika',            // mata_pelajaran
                'Guru Mapel',            // jabatan
                '081234567890',          // no_hp
            ], null, 'A2');

            // Data sampel baris 2 untuk guru
            $sheet->fromArray([
                '199002022015022002',   // nip
                'Siti Aminah, M.Pd',     // nama_lengkap
                'P',                     // jenis_kelamin
                'Bahasa Indonesia',      // mata_pelajaran
                'Wali Kelas',            // jabatan
                '081234567891',          // no_hp
            ], null, 'A3');
        } else {
            // Data sampel baris 1
            $sheet->fromArray([
                '1234567890',   // nis
                'Ali Ahmad',    // nama_lengkap
                '0012345678',   // nisn
                'Jakarta',      // tempat_lahir
                '01-01-2010',   // tanggal_lahir
                'L',            // jenis_kelamin
                'Jl. Merdeka No. 1, Jakarta', // alamat
                '081234567890', // no_hp
                '087654321098', // no_hp_ortu
                'X-A',          // kelas_nama
                '2025/2026',    // tahun_akademik_nama
            ], null, 'A2');

            // Data sampel baris 2
            $sheet->fromArray([
                '1234567891',   // nis
                'Budi Santoso', // nama_lengkap
                '0012345679',   // nisn
                'Bandung',      // tempat_lahir
                '15-06-2010',   // tanggal_lahir
                'L',            // jenis_kelamin
                'Jl. Sudirman No. 5, Bandung', // alamat
                '081987654321', // no_hp
                '082223344556', // no_hp_ortu
                'X-A',          // kelas_nama
                '2025/2026',    // tahun_akademik_nama
            ], null, 'A3');
        }

        // FORMAT SEMUA KOLOM DATA SEBAGAI TEXT (kunci utama agar NIS/NIP panjang tidak berubah scientific notation)
        $sheet->getStyle("A2:{$lastColumn}3")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        // Auto-size kolom
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Simpan ke file sementara
        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = $type === 'guru' ? "template_guru_{$timestamp}.xlsx" : "template_siswa_{$timestamp}.xlsx";
        $downloadName = $type === 'guru' ? 'template_guru.xlsx' : 'template_siswa.xlsx';
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

        return response()->download($fullPath, $downloadName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Buat Google Sheet baru via API dengan header standar, atau isi sheet yang sudah ada.
     *
     * @param  array  $credentials  Konfigurasi credentials Google API
     * @param  string|null  $spreadsheetId  Jika diberikan, gunakan spreadsheet yang sudah ada
     * @param  string  $type  Tipe data: 'siswa' atau 'guru'
     * @return array ['spreadsheet_id' => '...', 'url' => '...']
     *
     * @throws \RuntimeException Jika gagal membuat/mengisi spreadsheet
     */
    public function createGoogleSheet(array $credentials, ?string $spreadsheetId = null, string $type = 'siswa'): array
    {
        try {
            $googleSheetsService = new GoogleSheetsService;
            $client = $googleSheetsService->getClient($credentials, false);
            $service = new Sheets($client);

            $columns = $type === 'guru' ? $this->guruColumns : $this->standardColumns;
            $defaultTitle = $type === 'guru' ? 'Template Data Guru - ' . now()->format('d/m/Y') : 'Template Data Siswa - ' . now()->format('d/m/Y');
            $sheetNamePrefix = $type === 'guru' ? 'Template Data Guru' : 'Template Data Siswa';

            if ($spreadsheetId) {
                // Update spreadsheet yang sudah ada
                $spreadsheet = $service->spreadsheets->get($spreadsheetId);
                $spreadsheetTitle = $spreadsheet->getProperties()->getTitle();
            } else {
                // Buat spreadsheet baru
                $spreadsheet = new GoogleSpreadsheet([
                    'properties' => [
                        'title' => $defaultTitle,
                    ],
                ]);
                $spreadsheet = $service->spreadsheets->create($spreadsheet, [
                    'fields' => 'spreadsheetId,spreadsheetUrl',
                ]);
                $spreadsheetId = $spreadsheet->getSpreadsheetId();
                $spreadsheetTitle = $sheetNamePrefix;
                $spreadsheet = $service->spreadsheets->get($spreadsheetId);
            }

            // Dapatkan sheet pertama
            $sheets = $spreadsheet->getSheets();
            $firstSheet = ! empty($sheets) ? $sheets[0] : null;
            $firstSheetTitle = $firstSheet ? $firstSheet->getProperties()->getTitle() : 'Sheet1';
            $firstSheetId = $firstSheet ? $firstSheet->getProperties()->getSheetId() : 0;

            // Siapkan data header (baris pertama)
            $headerRow = [$columns];

            $valueRange = new ValueRange([
                'values' => $headerRow,
                'range' => $firstSheetTitle.'!A1:'.Coordinate::stringFromColumnIndex(count($columns)).'1',
            ]);

            // Tulis header ke sheet pertama
            $service->spreadsheets_values->update(
                $spreadsheetId,
                $firstSheetTitle.'!A1:'.Coordinate::stringFromColumnIndex(count($columns)).'1',
                $valueRange,
                ['valueInputOption' => 'RAW']
            );

            // Format header bold via Google Sheets API
            $requests = [
                new Request([
                    'repeatCell' => [
                        'range' => [
                            'sheetId' => $firstSheetId,
                            'startRowIndex' => 0,
                            'endRowIndex' => 1,
                            'startColumnIndex' => 0,
                            'endColumnIndex' => count($columns),
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

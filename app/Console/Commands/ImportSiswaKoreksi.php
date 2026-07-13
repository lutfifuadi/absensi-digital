<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Illuminate\Support\Facades\Log;

class ImportSiswaKoreksi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:siswa-koreksi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import file excel koreksi data siswa XII yang invalid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = base_path('planning/siswa-xii-invalid-koreksi.xlsx');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan di: {$filePath}");
            return 1;
        }

        $this->info("Memulai proses import file: {$filePath}");

        try {
            $import = new SiswaImport();
            $import->setFileName('siswa-xii-invalid-koreksi.xlsx');

            Excel::import($import, $filePath);

            $result = $import->getImportResult();

            $this->info("\nImport selesai!");
            $this->info("Berhasil: {$result['success']} baris");
            
            if ($result['failed'] > 0) {
                $this->error("Gagal: {$result['failed']} baris");
                $this->warn("\nDetail Kesalahan:");
                foreach ($result['errors'] as $err) {
                    $this->line(" - Baris {$err['row']} (NISN: {$err['nisn']}, Nama: {$err['nama']}): {$err['error']}");
                }
                return 1;
            } else {
                $this->info("Seluruh baris berhasil di-import tanpa error!");
                return 0;
            }

        } catch (ExcelValidationException $exception) {
            $this->error("Validasi file gagal. Silakan periksa data Anda.");
            $this->warn("\nDetail Validasi Gagal:");
            foreach ($exception->failures() as $failure) {
                $this->line(" - Baris {$failure->row()} (Kolom: {$failure->attribute()}): " . implode(', ', $failure->errors()));
            }
            return 1;
        } catch (\Throwable $e) {
            $this->error("Terjadi error sistem saat memproses import: " . $e->getMessage());
            Log::error("Console ImportSiswaKoreksi failed: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return 1;
        }
    }
}

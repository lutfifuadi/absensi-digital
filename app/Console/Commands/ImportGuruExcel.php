<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\User;
use App\Models\Guru;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ImportGuruExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:guru-excel {filepath=D:/Project/Website/MAN 1 Kota Bandung/Tahun Pelajaran 2025-2026/guru.xlsx : Path to the excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data guru dari file excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('filepath');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Membaca file: {$filePath}");

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getSheetByName('Lembar1');

            if (!$worksheet) {
                $this->error("Sheet 'Lembar1' tidak ditemukan di file Excel tersebut.");
                return Command::FAILURE;
            }

            $rows = $worksheet->toArray();

            if (empty($rows)) {
                $this->error("File Excel kosong.");
                return Command::FAILURE;
            }

            // Bersihkan header
            $headers = array_map(function($header) {
                return $header !== null ? trim($header) : '';
            }, $rows[0]);

            $nikIndex = array_search('NIK/NUPTK', $headers);
            $namaIndex = array_search('Nama', $headers);
            $lpIndex = array_search('L/P', $headers);
            $aksiIndex = array_search('Aksi', $headers);
            $passwordIndex = array_search('Password', $headers);

            if ($nikIndex === false || $namaIndex === false || $lpIndex === false || $aksiIndex === false) {
                $this->error("Kolom wajib (NIK/NUPTK, Nama, L/P, Aksi) tidak lengkap di file Excel.");
                return Command::FAILURE;
            }

            $importedCount = 0;
            $deletedCount = 0;
            $skippedCount = 0;

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                // Skip header (baris pertama)
                if ($index === 0) {
                    continue;
                }

                $nik = isset($row[$nikIndex]) ? trim($row[$nikIndex]) : '';
                $nama = isset($row[$namaIndex]) ? trim($row[$namaIndex]) : '';
                $lp = isset($row[$lpIndex]) ? trim($row[$lpIndex]) : '';
                $aksi = isset($row[$aksiIndex]) ? trim($row[$aksiIndex]) : '';
                $password = ($passwordIndex !== false && isset($row[$passwordIndex])) ? trim($row[$passwordIndex]) : '';

                // Jika NIK/NUPTK atau Nama kosong, skip baris tersebut.
                if (empty($nik) || empty($nama)) {
                    $skippedCount++;
                    continue;
                }

                $aksiUpper = strtoupper(trim($aksi));

                if ($aksiUpper === 'NON AKTIF') {
                    $user = User::where('username', $nik)->first();
                    if ($user) {
                        if ($user->guru) {
                            $user->guru->delete();
                        }
                        $user->delete();
                        $this->info("Menghapus guru nonaktif: {$nama}");
                        $deletedCount++;
                    } else {
                        $skippedCount++;
                    }
                } elseif ($aksiUpper === 'AKTIF') {
                    $email = strtolower($nik) . '@man1bdg.sch.id';
                    $passwordVal = !empty($password) ? Hash::make($password) : Hash::make('password123');

                    $user = User::updateOrCreate(
                        ['username' => $nik],
                        [
                            'name' => $nama,
                            'email' => $email,
                            'password' => $passwordVal,
                            'role' => User::ROLE_GURU,
                            'roles' => [User::ROLE_GURU],
                        ]
                    );

                    $jenisKelamin = !empty($lp) ? $lp : 'L';

                    Guru::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'nip' => $nik,
                            'nama_lengkap' => $nama,
                            'jenis_kelamin' => $jenisKelamin,
                            'mata_pelajaran' => '-',
                            'jabatan' => 'Guru',
                            'status' => 'aktif',
                        ]
                    );

                    $this->info("Berhasil mengimport/update guru aktif: {$nama}");
                    $importedCount++;
                } else {
                    $skippedCount++;
                }
            }

            DB::commit();

            $this->info("Import selesai!");
            $this->info("Berhasil diimport/update (aktif): {$importedCount}");
            $this->info("Berhasil dihapus (nonaktif): {$deletedCount}");
            $this->info("Dilewati/Kosong/Aksi tidak valid: {$skippedCount}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Terjadi kesalahan: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

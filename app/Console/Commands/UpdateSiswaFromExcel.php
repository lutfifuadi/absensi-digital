<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Kelas;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class UpdateSiswaFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-siswa-from-excel 
                            {file? : Path lengkap ke file Excel}
                            {--dry-run : Jalankan simulasi tanpa menyimpan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membaca file Excel siswa XII dan mengupdate kelas, nis, dan nisn di database berdasarkan nama siswa';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file') ?? 'D:\\Project\\Website\\MAN 1 Kota Bandung\\Tahun Pelajaran 2026-2027\\KELAS_XII_TA_2026_2027.xlsx';
        $dryRun = $this->option('dry-run');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan di: {$filePath}");
            return 1;
        }

        $this->info("Membaca file Excel dari: {$filePath}");
        if ($dryRun) {
            $this->warn("Menjalankan dalam mode DRY-RUN (simulasi, tidak menyimpan ke DB).");
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheetNames = $spreadsheet->getSheetNames();
            $this->info("Sheet yang ditemukan: " . implode(', ', $sheetNames));

            $successKelasCount = 0;
            $successNisNisnCount = 0;
            $bothUpdatedCount = 0;
            $noChangeCount = 0;
            $notFoundCount = 0;
            $multipleFoundCount = 0;

            // Ambil semua kelas untuk pencocokan cepat
            $dbKelas = Kelas::all();
            $kelasMap = [];
            foreach ($dbKelas as $k) {
                // Simpan kelas berdasarkan nama asli
                $kelasMap[$k->nama] = $k->id;
                // Simpan juga kelas dengan format alternatif (tanpa titik/spasi/hubung)
                $normalizedDb = strtoupper(str_replace(['.', '-', ' '], '', $k->nama));
                $kelasMap[$normalizedDb] = $k->id;
            }

            DB::beginTransaction();

            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                $sheetName = $sheet->getTitle();
                
                // Skip sheet yang bukan data kelas kelas XII, seperti "KLAFER" atau "Sheet1" jika ada
                // Tapi mari kita proses sheet yang berformat XII.F.xxx atau XII.F-xxx atau jika nama sheet mengandung XII
                if (stripos($sheetName, 'XII') === false) {
                    $this->info("Men-skip sheet: {$sheetName}");
                    continue;
                }

                $this->info("Memproses sheet: {$sheetName}");

                $rows = $sheet->toArray();
                if (empty($rows)) {
                    continue;
                }

                // Cari header row
                $headerRowIndex = -1;
                $headers = [];
                foreach ($rows as $index => $row) {
                    $cleanRow = array_filter(array_map('trim', $row));
                    $hasNis = false;
                    $hasNama = false;
                    foreach ($cleanRow as $cell) {
                        if (stripos($cell, 'nis') !== false) $hasNis = true;
                        if (stripos($cell, 'nama') !== false) $hasNama = true;
                    }
                    if ($hasNis && $hasNama) {
                        $headerRowIndex = $index;
                        $headers = array_map(function($h) {
                            return strtolower(trim($h));
                        }, $row);
                        break;
                    }
                }

                if ($headerRowIndex === -1) {
                    $this->warn("Header tidak ditemukan di sheet {$sheetName}. Mencoba baris pertama.");
                    $headers = array_map(function($h) {
                        return strtolower(trim($h));
                    }, $rows[0]);
                    $headerRowIndex = 0;
                }

                // Tentukan index kolom
                $colNis = -1;
                $colNisn = -1;
                $colNama = -1;
                $colKelas = -1;

                foreach ($headers as $idx => $header) {
                    if (empty($header)) continue;
                    if ($header === 'nis') $colNis = $idx;
                    elseif ($header === 'nisn') $colNisn = $idx;
                    elseif ($header === 'nama' || stripos($header, 'nama') !== false) $colNama = $idx;
                    elseif ($header === 'kelas' || stripos($header, 'kelas') !== false) $colKelas = $idx;
                }

                // Fallback untuk NIS/NISN jika belum pas
                if ($colNis === -1) {
                    foreach ($headers as $idx => $header) {
                        if (stripos($header, 'nis') !== false && stripos($header, 'nisn') === false) {
                            $colNis = $idx;
                            break;
                        }
                    }
                }
                if ($colNisn === -1) {
                    foreach ($headers as $idx => $header) {
                        if (stripos($header, 'nisn') !== false) {
                            $colNisn = $idx;
                            break;
                        }
                    }
                }

                if ($colNama === -1) {
                    $this->error("Kolom nama tidak ditemukan di sheet {$sheetName}. Skip sheet.");
                    continue;
                }

                // Default kelas dari nama sheet jika kolom kelas tidak ditemukan
                $defaultKelasExcel = null;
                if ($colKelas === -1) {
                    $defaultKelasExcel = $sheetName;
                }

                for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    $cleanRow = array_filter(array_map('trim', $row));
                    if (empty($cleanRow)) continue;

                    $namaExcel = trim((string)($row[$colNama] ?? ''));
                    if (empty($namaExcel)) continue;

                    $nisExcel = $colNis !== -1 ? trim((string)($row[$colNis] ?? '')) : null;
                    $nisnExcel = $colNisn !== -1 ? trim((string)($row[$colNisn] ?? '')) : null;
                    $kelasExcel = $colKelas !== -1 ? trim((string)($row[$colKelas] ?? '')) : $defaultKelasExcel;

                    // Cari siswa di DB berdasarkan nama lengkap
                    $siswaQuery = Siswa::where('nama_lengkap', $namaExcel);
                    $siswaCount = $siswaQuery->count();

                    if ($siswaCount === 0) {
                        // Coba case insensitive / trimmed
                        $siswaQuery = Siswa::whereRaw('LOWER(nama_lengkap) = ?', [strtolower($namaExcel)]);
                        $siswaCount = $siswaQuery->count();
                    }

                    if ($siswaCount === 0) {
                        $notFoundCount++;
                        $this->warn(" - Siswa '{$namaExcel}' tidak ditemukan di database.");
                        continue;
                    }

                    if ($siswaCount > 1) {
                        $multipleFoundCount++;
                        $this->error(" - Ditemukan {$siswaCount} siswa dengan nama '{$namaExcel}' di database (Ambiguitas).");
                        continue;
                    }

                    $siswa = $siswaQuery->first();

                    // Tentukan kelas_id berdasarkan nama kelas
                    $kelasIdBaru = null;
                    if (!empty($kelasExcel)) {
                        // Sesuaikan format titik (XII.F.1) menjadi format hubung (XII.F-1)
                        $kelasDbFormatted = preg_replace('/\.(\d+)$/', '-$1', $kelasExcel);

                        // Coba cari kelas di database
                        if (isset($kelasMap[$kelasDbFormatted])) {
                            $kelasIdBaru = $kelasMap[$kelasDbFormatted];
                        } else {
                            // Coba normalized match
                            $normalizedExcel = strtoupper(str_replace(['.', '-', ' '], '', $kelasExcel));
                            if (isset($kelasMap[$normalizedExcel])) {
                                $kelasIdBaru = $kelasMap[$normalizedExcel];
                            } else {
                                $this->warn(" - Kelas '{$kelasExcel}' (diformat: {$kelasDbFormatted}) tidak ditemukan di database.");
                            }
                        }
                    }

                    // Cek apakah NISN baru ini sudah dipakai siswa lain untuk menghindari unique constraint violation
                    if ($nisnExcel !== null && $nisnExcel !== '' && $siswa->nisn !== $nisnExcel) {
                        $duplicateNisn = Siswa::where('nisn', $nisnExcel)->where('id', '!=', $siswa->id)->first();
                        if ($duplicateNisn) {
                            $this->error(" - Gagal update NISN siswa '{$siswa->nama_lengkap}': NISN '{$nisnExcel}' sudah digunakan oleh siswa '{$duplicateNisn->nama_lengkap}' (ID {$duplicateNisn->id}).");
                            // Kita hanya batalkan update NISN, atau kita tetap update data lain?
                            // Agar aman, jangan update NISN-nya
                            $nisnExcel = null; 
                        }
                    }

                    // Cek apakah NIS baru ini sudah dipakai siswa lain
                    if ($nisExcel !== null && $nisExcel !== '' && $siswa->nis !== $nisExcel) {
                        $duplicateNis = Siswa::where('nis', $nisExcel)->where('id', '!=', $siswa->id)->first();
                        if ($duplicateNis) {
                            $this->error(" - Gagal update NIS siswa '{$siswa->nama_lengkap}': NIS '{$nisExcel}' sudah digunakan oleh siswa '{$duplicateNis->nama_lengkap}' (ID {$duplicateNis->id}).");
                            $nisExcel = null;
                        }
                    }

                    // Cek perubahan data
                    $updateData = [];
                    $isKelasChanged = false;
                    $isNisNisnChanged = false;

                    if ($kelasIdBaru && $siswa->kelas_id != $kelasIdBaru) {
                        $updateData['kelas_id'] = $kelasIdBaru;
                        $isKelasChanged = true;
                    }

                    if ($nisExcel !== null && $nisExcel !== '' && $siswa->nis !== $nisExcel) {
                        $updateData['nis'] = $nisExcel;
                        $isNisNisnChanged = true;
                    }

                    if ($nisnExcel !== null && $nisnExcel !== '' && $siswa->nisn !== $nisnExcel) {
                        $updateData['nisn'] = $nisnExcel;
                        $isNisNisnChanged = true;
                    }

                    if (!empty($updateData)) {
                        if (!$dryRun) {
                            $siswa->update($updateData);
                        }

                        if ($isKelasChanged && $isNisNisnChanged) {
                            $bothUpdatedCount++;
                            $this->line(" - Update {$siswa->nama_lengkap}: Kelas ke ID {$kelasIdBaru}, NIS/NISN updated.");
                        } elseif ($isKelasChanged) {
                            $successKelasCount++;
                            $this->line(" - Update {$siswa->nama_lengkap}: Kelas ke ID {$kelasIdBaru}.");
                        } else {
                            $successNisNisnCount++;
                            $this->line(" - Update {$siswa->nama_lengkap}: NIS/NISN updated.");
                        }
                    } else {
                        $noChangeCount++;
                    }
                }
            }

            if ($dryRun) {
                DB::rollBack();
                $this->info("\n[DRY RUN] Transaksi di-rollback. Tidak ada perubahan yang disimpan ke database.");
            } else {
                DB::commit();
                $this->info("\nTransaksi berhasil di-commit ke database.");
            }

            $this->info("\n=== REKAPITULASI ===");
            $this->info("Siswa berhasil diupdate KELAS saja      : " . $successKelasCount);
            $this->info("Siswa berhasil diupdate NIS/NISN saja   : " . $successNisNisnCount);
            $this->info("Siswa berhasil diupdate KEDUA-DUANYA    : " . $bothUpdatedCount);
            $this->info("Siswa TIDAK ADA PERUBAHAN data          : " . $noChangeCount);
            $this->info("Siswa TIDAK DITEMUKAN di database       : " . $notFoundCount);
            $this->info("Siswa GANDA/AMBIGU di database          : " . $multipleFoundCount);
            $this->info("Total Siswa yang kelasnya ter-update    : " . ($successKelasCount + $bothUpdatedCount));
            $this->info("Total Siswa yang NIS/NISN-nya ter-update: " . ($successNisNisnCount + $bothUpdatedCount));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Terjadi error: " . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}

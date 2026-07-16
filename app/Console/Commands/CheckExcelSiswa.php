<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Kelas;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CheckExcelSiswa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-excel-siswa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pengecekan data siswa kelas XII dari Excel terhadap database';

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

        $this->info("Membaca file excel: {$filePath}");

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheetNames = $spreadsheet->getSheetNames();
            $this->info("Daftar sheet yang tersedia: " . implode(', ', $sheetNames));

            // Pilih sheet KLAFER jika ada, jika tidak, pilih Sheet1 atau sheet pertama
            $sheetName = in_array('KLAFER', $sheetNames) ? 'KLAFER' : (in_array('Sheet1', $sheetNames) ? 'Sheet1' : $sheetNames[0]);
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $this->info("Menggunakan sheet: {$sheetName}");

            $rows = $sheet->toArray();
            if (empty($rows)) {
                $this->error("Sheet kosong.");
                return 1;
            }

            // Temukan header row
            $headerRowIndex = 0;
            $headers = [];
            foreach ($rows as $index => $row) {
                // Bersihkan null
                $cleanRow = array_filter(array_map('trim', $row));
                // Biasanya header mengandung NISN, NIS, atau Nama
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

            if (empty($headers)) {
                // Fallback ke baris pertama
                $headers = array_map(function($h) {
                    return strtolower(trim($h));
                }, $rows[0]);
                $headerRowIndex = 0;
            }

            $this->info("Header ditemukan di baris " . ($headerRowIndex + 1) . ": " . implode(', ', array_filter($headers)));

            // Temukan index kolom
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

            // Cari fallback jika belum ketemu pas
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

            $this->info("Mapping Kolom: NIS => " . ($colNis !== -1 ? "Index $colNis ({$headers[$colNis]})" : "Tidak ditemukan") .
                        ", NISN => " . ($colNisn !== -1 ? "Index $colNisn ({$headers[$colNisn]})" : "Tidak ditemukan") .
                        ", Nama => " . ($colNama !== -1 ? "Index $colNama ({$headers[$colNama]})" : "Tidak ditemukan") .
                        ", Kelas => " . ($colKelas !== -1 ? "Index $colKelas ({$headers[$colKelas]})" : "Tidak ditemukan"));

            // Get all students in database for comparison and verification
            $siswaPenampungId = 80;
            $siswaPenampung = Siswa::where('kelas_id', $siswaPenampungId)->get();
            $this->info("Siswa di kelas penampung 'XII' (ID {$siswaPenampungId}) di DB: " . $siswaPenampung->count());

            // Ambil semua kelas target XII di DB (XII.F-1 s.d XII.F-12)
            $kelasTarget = Kelas::where('nama', 'like', 'XII.F%')->get();
            $this->info("Kelas target XII di DB: " . $kelasTarget->count() . " kelas");
            foreach ($kelasTarget as $k) {
                $this->line(" - ID {$k->id}: {$k->nama}");
            }

            $excelData = [];
            $foundInDb = 0;
            $notFoundInDb = 0;
            $matchedIds = [];
            $unmatchedExcelRows = [];

            $nisnList = [];
            $nisList = [];

            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Pastikan baris tidak kosong sama sekali
                $cleanRow = array_filter(array_map('trim', $row));
                if (empty($cleanRow)) continue;

                $nis = $colNis !== -1 ? trim((string)($row[$colNis] ?? '')) : '';
                $nisn = $colNisn !== -1 ? trim((string)($row[$colNisn] ?? '')) : '';
                $nama = $colNama !== -1 ? trim((string)($row[$colNama] ?? '')) : '';
                $kelasExcel = $colKelas !== -1 ? trim((string)($row[$colKelas] ?? '')) : '';

                if (empty($nis) && empty($nisn) && empty($nama)) {
                    continue; // Skip baris kosong
                }

                // Cari di DB
                $dbSiswa = null;
                if (!empty($nisn)) {
                    $dbSiswa = Siswa::where('nisn', $nisn)->first();
                }
                if (!$dbSiswa && !empty($nis)) {
                    $dbSiswa = Siswa::where('nis', $nis)->first();
                }
                if (!$dbSiswa && !empty($nama)) {
                    // Cari berdasarkan nama kemiripan
                    $dbSiswa = Siswa::where('nama_lengkap', 'like', "%{$nama}%")->first();
                }

                if ($dbSiswa) {
                    $foundInDb++;
                    $matchedIds[] = $dbSiswa->id;
                    $excelData[] = [
                        'row' => $i + 1,
                        'excel_nis' => $nis,
                        'excel_nisn' => $nisn,
                        'excel_nama' => $nama,
                        'excel_kelas' => $kelasExcel,
                        'db_id' => $dbSiswa->id,
                        'db_nama' => $dbSiswa->nama_lengkap,
                        'db_kelas_id' => $dbSiswa->kelas_id,
                        'db_kelas_nama' => $dbSiswa->kelas ? $dbSiswa->kelas->nama : 'Tanpa Kelas',
                    ];
                } else {
                    $notFoundInDb++;
                    $unmatchedExcelRows[] = [
                        'row' => $i + 1,
                        'nis' => $nis,
                        'nisn' => $nisn,
                        'nama' => $nama,
                        'kelas' => $kelasExcel
                    ];
                }
            }

            $totalExcel = count($excelData) + count($unmatchedExcelRows);
            $this->info("\n=== HASIL PENGECEKAN ===");
            $this->info("1. Total siswa di Excel (yang valid dibaca): " . $totalExcel);
            $this->info("2. Siswa Excel ditemukan di DB: " . $foundInDb);
            $this->info("3. Siswa Excel TIDAK ditemukan di DB: " . $notFoundInDb);

            if ($notFoundInDb > 0) {
                $this->warn("\nSiswa Excel yang tidak ditemukan di DB:");
                foreach (array_slice($unmatchedExcelRows, 0, 10) as $unmatched) {
                    $this->line(" - Baris {$unmatched['row']}: NIS={$unmatched['nis']}, NISN={$unmatched['nisn']}, Nama='{$unmatched['nama']}', Kelas='{$unmatched['kelas']}'");
                }
                if (count($unmatchedExcelRows) > 10) {
                    $this->line(" ... dan " . (count($unmatchedExcelRows) - 10) . " siswa lainnya.");
                }
            }

            // Apakah siswa penampung XII semuanya ada di Excel?
            $siswaPenampungMatched = 0;
            $siswaPenampungNotMatched = 0;
            $siswaPenampungNotInExcel = [];

            foreach ($siswaPenampung as $sp) {
                if (in_array($sp->id, $matchedIds)) {
                    $siswaPenampungMatched++;
                } else {
                    $siswaPenampungNotMatched++;
                    $siswaPenampungNotInExcel[] = $sp;
                }
            }

            $this->info("\n=== KELAS PENAMPUNG XII ===");
            $this->info("Total siswa saat ini di kelas penampung 'XII' (ID 80): " . $siswaPenampung->count());
            $this->info("Jumlah siswa penampung 'XII' yang terdaftar di Excel: " . $siswaPenampungMatched);
            $this->info("Jumlah siswa penampung 'XII' yang TIDAK ada di Excel: " . $siswaPenampungNotMatched);

            if ($siswaPenampungNotMatched > 0) {
                $this->warn("\nSiswa penampung 'XII' yang tidak ada di Excel:");
                foreach (array_slice($siswaPenampungNotInExcel, 0, 10) as $sp) {
                    $this->line(" - ID {$sp->id}: NIS={$sp->nis}, NISN={$sp->nisn}, Nama='{$sp->nama_lengkap}'");
                }
                if (count($siswaPenampungNotInExcel) > 10) {
                    $this->line(" ... dan " . (count($siswaPenampungNotInExcel) - 10) . " siswa lainnya.");
                }
            }

            // Hitung rencana pemindahan siswa ke kelas XII target
            $kelasMap = [];
            foreach ($kelasTarget as $k) {
                // standard nama kelas: XII.F-1, XII.F-2, dll.
                // format nama di excel: XII F-1, XII F.1, XII F 1, XII.F-1, dll.
                $kelasMap[strtoupper(str_replace([' ', '.', '-'], '', $k->nama))] = $k;
            }

            $this->info("\n=== RENCANA UPDATE KELAS ===");
            $readyToUpdate = 0;
            $classTargetNotFound = 0;
            $cantUpdateNoDb = 0;
            
            $kelasDistribution = [];

            foreach ($excelData as $item) {
                $rawKelasExcel = strtoupper(str_replace([' ', '.', '-'], '', $item['excel_kelas']));
                
                // Coba cocokkan dengan map
                $matchedKelasObj = null;
                // coba match langsung
                if (isset($kelasMap[$rawKelasExcel])) {
                    $matchedKelasObj = $kelasMap[$rawKelasExcel];
                } else {
                    // Coba bersihkan lagi, misal "XIIF1" -> "XIIF-1"
                    // Cari yang paling mendekati
                    foreach ($kelasMap as $cleanDbKey => $kelasObj) {
                        if ($cleanDbKey === $rawKelasExcel || str_contains($cleanDbKey, $rawKelasExcel) || str_contains($rawKelasExcel, $cleanDbKey)) {
                            $matchedKelasObj = $kelasObj;
                            break;
                        }
                    }
                }

                if ($matchedKelasObj) {
                    $readyToUpdate++;
                    if (!isset($kelasDistribution[$matchedKelasObj->nama])) {
                        $kelasDistribution[$matchedKelasObj->nama] = 0;
                    }
                    $kelasDistribution[$matchedKelasObj->nama]++;
                } else {
                    $classTargetNotFound++;
                    $this->warn("Baris {$item['row']}: Target kelas '{$item['excel_kelas']}' tidak dapat dicocokkan dengan kelas DB.");
                }
            }

            $this->info("Jumlah siswa yang siap diupdate kelasnya: " . $readyToUpdate);
            $this->info("Jumlah siswa yang target kelasnya tidak ditemukan di DB: " . $classTargetNotFound);

            $this->info("\nDistribusi kelas baru jika diupdate:");
            ksort($kelasDistribution);
            foreach ($kelasDistribution as $kNama => $count) {
                $this->line(" - {$kNama}: {$count} siswa");
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->line($e->getTraceAsString());
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Kelas;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AnalyzeMatches extends Command
{
    protected $signature = 'app:analyze-matches';
    protected $description = 'Menganalisis pencocokan NIS, NISN, dan Nama untuk 427 siswa Excel';

    public function handle()
    {
        $filePath = 'D:\\Project\\Website\\MAN 1 Kota Bandung\\Tahun Pelajaran 2026-2027\\KELAS_XII_TA_2026_2027.xlsx';
        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan.");
            return 1;
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheetNames = $spreadsheet->getSheetNames();

            // Kumpulkan data excel
            $excelData = [];
            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                $sheetName = $sheet->getTitle();
                if (stripos($sheetName, 'XII') === false) {
                    continue;
                }
                
                $rows = $sheet->toArray();
                if (empty($rows)) continue;

                // Cari header row
                $headerRowIndex = -1;
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
                        break;
                    }
                }

                if ($headerRowIndex === -1) $headerRowIndex = 0;

                $colNis = -1;
                $colNisn = -1;
                $colNama = -1;
                $rowZero = $rows[$headerRowIndex];
                foreach ($rowZero as $idx => $cell) {
                    $cellLower = strtolower(trim($cell));
                    if ($cellLower === 'nis') $colNis = $idx;
                    elseif ($cellLower === 'nisn') $colNisn = $idx;
                    elseif ($cellLower === 'nama' || stripos($cellLower, 'nama') !== false) $colNama = $idx;
                }

                // Fallback NIS/NISN
                if ($colNis === -1) {
                    foreach ($rowZero as $idx => $cell) {
                        if (stripos($cell, 'nis') !== false && stripos($cell, 'nisn') === false) {
                            $colNis = $idx;
                            break;
                        }
                    }
                }
                if ($colNisn === -1) {
                    foreach ($rowZero as $idx => $cell) {
                        if (stripos($cell, 'nisn') !== false) {
                            $colNisn = $idx;
                            break;
                        }
                    }
                }

                for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                    $namaExcel = trim((string)($rows[$i][$colNama] ?? ''));
                    if (empty($namaExcel)) continue;
                    
                    $nisExcel = $colNis !== -1 ? trim((string)($rows[$i][$colNis] ?? '')) : '';
                    $nisnExcel = $colNisn !== -1 ? trim((string)($rows[$i][$colNisn] ?? '')) : '';

                    $excelData[] = [
                        'sheet' => $sheetName,
                        'nama' => $namaExcel,
                        'nis' => $nisExcel,
                        'nisn' => $nisnExcel,
                    ];
                }
            }

            $this->info("Berhasil membaca " . count($excelData) . " siswa dari Excel.");

            // Ambil semua siswa di DB (termasuk kelas penampung XII dan kelas target XII.F-*)
            $siswaPenampungId = 80;
            $kelasTargetIds = Kelas::where('nama', 'like', 'XII.F%')->pluck('id')->toArray();
            $allowedKelasIds = array_merge([$siswaPenampungId], $kelasTargetIds);

            $dbSiswa = Siswa::whereIn('kelas_id', $allowedKelasIds)->get();
            $this->info("Total siswa XII di DB: " . $dbSiswa->count());

            // Helper function untuk normalisasi nama (fuzzy matching)
            $normalizeName = function($name) {
                $name = strtolower($name);
                // Hapus gelar yang umum di akhir/awal (jika ada)
                // Hapus tanda petik, spasi ganda, tanda hubung, dll.
                $name = preg_replace('/[\'`"’\-\.]/', ' ', $name);
                $name = preg_replace('/\s+/', ' ', $name);
                return trim($name);
            };

            $matchedCount = 0;
            $ambiguousCount = 0;
            $unmatchedExcel = [];
            $matchedDbIds = [];

            foreach ($excelData as $item) {
                $excelNameNormalized = $normalizeName($item['nama']);
                
                // Pencocokan bertingkat
                $candidates = collect();

                // 1. Coba NISN (jika ada dan tidak kosong)
                if (!empty($item['nisn'])) {
                    $c = $dbSiswa->firstWhere('nisn', $item['nisn']);
                    if ($c) $candidates->push($c);
                }

                // 2. Coba NIS (jika ada dan tidak kosong, dan belum ketemu dengan NISN)
                if ($candidates->isEmpty() && !empty($item['nis'])) {
                    $c = $dbSiswa->firstWhere('nis', $item['nis']);
                    if ($c) $candidates->push($c);
                }

                // 3. Coba Nama Lengkap Persis (case-insensitive)
                if ($candidates->isEmpty()) {
                    $cs = $dbSiswa->filter(function($s) use ($item) {
                        return strcasecmp($s->nama_lengkap, $item['nama']) === 0;
                    });
                    foreach ($cs as $c) $candidates->push($c);
                }

                // 4. Coba Fuzzy Matching Nama
                if ($candidates->isEmpty()) {
                    $cs = $dbSiswa->filter(function($s) use ($excelNameNormalized, $normalizeName) {
                        return $normalizeName($s->nama_lengkap) === $excelNameNormalized;
                    });
                    foreach ($cs as $c) $candidates->push($c);
                }

                // 5. Coba pencocokan parsial / like (jika masih kosong)
                if ($candidates->isEmpty()) {
                    $cs = $dbSiswa->filter(function($s) use ($excelNameNormalized, $normalizeName) {
                        $dbNormalized = $normalizeName($s->nama_lengkap);
                        return str_contains($dbNormalized, $excelNameNormalized) || str_contains($excelNameNormalized, $dbNormalized);
                    });
                    foreach ($cs as $c) $candidates->push($c);
                }

                // Hapus duplikasi kandidat berdasarkan ID
                $candidates = $candidates->unique('id');

                if ($candidates->count() === 1) {
                    $matchedCount++;
                    $matchedDbIds[] = $candidates->first()->id;
                } elseif ($candidates->count() > 1) {
                    $ambiguousCount++;
                    $this->warn("Ambigu: {$item['nama']} (Excel: NISN={$item['nisn']}, NIS={$item['nis']})");
                    foreach ($candidates as $cand) {
                        $this->line("   -> DB: ID={$cand->id}, Nama='{$cand->nama_lengkap}', NISN={$cand->nisn}, NIS={$cand->nis}");
                    }
                } else {
                    $unmatchedExcel[] = $item;
                }
            }

            $this->info("\n=== ANALISIS PENCOCOKAN ===");
            $this->info("1. Berhasil dicocokkan unik: $matchedCount");
            $this->info("2. Ambigu: $ambiguousCount");
            $this->info("3. Tidak ditemukan: " . count($unmatchedExcel));

            if (count($unmatchedExcel) > 0) {
                $this->warn("\nSiswa Excel yang tidak cocok:");
                foreach ($unmatchedExcel as $idx => $item) {
                    $this->line(" - #" . ($idx+1) . " Sheet {$item['sheet']}: '{$item['nama']}' (NISN: '{$item['nisn']}', NIS: '{$item['nis']}')");
                }
            }

            // Cari tahu siswa DB mana saja yang tidak cocok
            $unmatchedDb = $dbSiswa->filter(function($s) use ($matchedDbIds) {
                return !in_array($s->id, $matchedDbIds);
            });

            $this->info("\nSiswa DB (total " . $unmatchedDb->count() . ") yang tidak tercocokkan dengan Excel:");
            foreach ($unmatchedDb as $s) {
                $this->line(" - ID {$s->id}: '{$s->nama_lengkap}' (NISN: '{$s->nisn}', NIS: '{$s->nis}', Kelas ID: {$s->kelas_id})");
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}

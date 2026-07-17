<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Kelas;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AnalyzeMatchesXumum extends Command
{
    protected $signature = 'app:analyze-matches-xumum';
    protected $description = 'Menganalisis pencocokan NIS, NISN, dan Nama untuk 473 siswa kelas X.UMUM dengan file Excel';

    public function handle()
    {
        $filePath = 'D:\\Project\\Website\\MAN 1 Kota Bandung\\Tahun Pelajaran 2026-2027\\DATA-LENGKAP-SISWA-KELAS-X-2026-2027.xlsx';
        if (!file_exists($filePath)) {
            $this->error("File Excel tidak ditemukan di: " . $filePath);
            return 1;
        }

        $this->info("Membaca file excel: " . $filePath);

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            if (empty($rows)) {
                $this->error("Sheet kosong.");
                return 1;
            }

            // Cari header
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

            if ($headerRowIndex === -1) {
                $headerRowIndex = 0;
            }

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

            // Fallbacks
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

            // Kumpulkan data excel
            $excelData = [];
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $namaExcel = trim((string)($rows[$i][$colNama] ?? ''));
                if (empty($namaExcel)) continue;
                
                $nisExcel = $colNis !== -1 ? trim((string)($rows[$i][$colNis] ?? '')) : '';
                $nisnExcel = $colNisn !== -1 ? trim((string)($rows[$i][$colNisn] ?? '')) : '';

                // Bersihkan NIS/NISN dari format scientific notation dll jika perlu
                $excelData[] = [
                    'nama' => $namaExcel,
                    'nis' => $nisExcel,
                    'nisn' => $nisnExcel,
                ];
            }

            $this->info("Berhasil membaca " . count($excelData) . " siswa dari Excel.");

            // Ambil data DB kelas X.UMUM
            $kelas = Kelas::where('nama', 'X.UMUM')->first();
            if (!$kelas) {
                $this->error("Kelas X.UMUM tidak ditemukan di database.");
                return 1;
            }

            $dbSiswa = Siswa::where('kelas_id', $kelas->id)->get();
            $this->info("Total siswa X.UMUM di DB: " . $dbSiswa->count());

            $normalizeName = function($name) {
                $name = strtolower($name);
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

            $unmatchedDb = $dbSiswa->filter(function($s) use ($matchedDbIds) {
                return !in_array($s->id, $matchedDbIds);
            });

            $this->info("\n=== HASIL PENCOCOKAN ===");
            $this->info("1. Siswa Excel Terbaca: " . count($excelData));
            $this->info("2. Siswa DB X.UMUM: " . $dbSiswa->count());
            $this->info("3. Cocok unik: $matchedCount");
            $this->info("4. Ambigu: $ambiguousCount");
            $this->info("5. Excel tidak ada di DB: " . count($unmatchedExcel));
            $this->info("6. DB tidak ada di Excel: " . $unmatchedDb->count());

            if (count($unmatchedExcel) > 0) {
                $this->warn("\nSiswa Excel yang tidak cocok ke DB (sampel 10):");
                foreach (array_slice($unmatchedExcel, 0, 10) as $idx => $item) {
                    $this->line(" - #" . ($idx+1) . ": '{$item['nama']}' (NISN: '{$item['nisn']}', NIS: '{$item['nis']}')");
                }
            }

            if ($unmatchedDb->count() > 0) {
                $this->warn("\nSiswa DB yang tidak cocok ke Excel (sampel 10):");
                $i = 1;
                foreach ($unmatchedDb->take(10) as $s) {
                    $this->line(" - #" . $i++ . ": '{$s->nama_lengkap}' (NISN: '{$s->nisn}', NIS: '{$s->nis}')");
                }
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}

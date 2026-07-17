<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Kelas;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CompareSiswaXiExcelDb extends Command
{
    protected $signature = 'app:compare-siswa-xi';
    protected $description = 'Bandingkan data siswa kelas XI dari file Excel dengan database';

    public function handle()
    {
        $filePath = 'D:\\Project\\Website\\MAN 1 Kota Bandung\\Tahun Pelajaran 2026-2027\\PENEMPATAN-KELAS-XI-2026-2027.xlsx';
        if (!file_exists($filePath)) {
            $this->error("File Excel tidak ditemukan di: " . $filePath);
            return 1;
        }

        $this->info("Membaca file excel: " . $filePath);

        try {
            $spreadsheet = IOFactory::load($filePath);
            $excelData = [];

            // Loop all sheets
            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                $sheetName = $sheet->getTitle();
                $rows = $sheet->toArray();
                if (empty($rows)) continue;

                // Cari header row
                $headerRowIndex = -1;
                foreach ($rows as $index => $row) {
                    $cleanRow = array_filter(array_map('trim', (array)$row));
                    $hasNis = false;
                    $hasNama = false;
                    foreach ($cleanRow as $cell) {
                        if (stripos($cell, 'nis') !== false) $hasNis = true;
                        if (stripos($cell, 'nama') !== false) $hasNama = true;
                    }
                    // Di sheet XI F1, baris index 3 berisi NO, NAMA, KELAS X, KELAS XI, JK
                    // Tidak ada NIS di situ, jadi kita harus cocokkan "nama" saja atau structural check
                    if (in_array('NAMA', $cleanRow) || in_array('Nama', $cleanRow) || $hasNama) {
                        $headerRowIndex = $index;
                        break;
                    }
                }

                if ($headerRowIndex === -1) {
                    continue; // Skip jika tidak ada header
                }

                $colNis = -1;
                $colNisn = -1;
                $colNama = -1;
                $rowZero = (array)$rows[$headerRowIndex];

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

            // Get all students in DB
            $dbSiswa = Siswa::with('kelas')->get();
            $this->info("Total siswa di DB: " . $dbSiswa->count());

            $normalizeName = function($name) {
                $name = strtolower($name);
                $name = preg_replace('/[\'`"’\-\.]/', ' ', $name);
                $name = preg_replace('/\s+/', ' ', $name);
                return trim($name);
            };

            $matchedCount = 0;
            $unmatchedExcel = [];
            $matchedDbIds = [];

            foreach ($excelData as $item) {
                $excelNameNormalized = $normalizeName($item['nama']);
                $candidates = collect();

                // 1. Coba NISN
                if (!empty($item['nisn'])) {
                    $c = $dbSiswa->firstWhere('nisn', $item['nisn']);
                    if ($c) $candidates->push($c);
                }

                // 2. Coba NIS
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

                // Hapus duplikasi kandidat
                $candidates = $candidates->unique('id');

                if ($candidates->count() === 1) {
                    $matchedCount++;
                    $matchedDbIds[] = $candidates->first()->id;
                } else {
                    // Coba fuzzy match levenshtein untuk siswa yang tidak cocok
                    $bestMatch = null;
                    $shortestDistance = -1;

                    foreach ($dbSiswa as $siswaDb) {
                        $dbNameNormalized = $normalizeName($siswaDb->nama_lengkap);
                        $dist = levenshtein($excelNameNormalized, $dbNameNormalized);

                        if ($dist <= 8) { // Perluas threshold untuk singkatan & nama panjang
                            if ($shortestDistance === -1 || $dist < $shortestDistance) {
                                $shortestDistance = $dist;
                                $bestMatch = $siswaDb;
                            }
                        }
                    }

                    // Tambahan similar_text check untuk fallback pencocokan persentase
                    $percent = 0;
                    if ($bestMatch) {
                        similar_text($excelNameNormalized, $normalizeName($bestMatch->nama_lengkap), $percent);
                    }

                    // Jika kemiripan di bawah 50%, anggap tidak ditemukan yang mirip
                    // Kecuali jika jarak levenshtein sangat kecil (<= 2)
                    if ($percent < 50 && $shortestDistance > 2) {
                        $bestMatch = null;
                        $shortestDistance = -1;
                    }

                    $unmatchedExcel[] = [
                        'excel' => $item,
                        'best_match' => $bestMatch,
                        'distance' => $shortestDistance,
                        'percent' => $percent,
                    ];
                }
            }

            $this->info("\n=== HASIL PENCOCOKAN ===");
            $this->info("Cocok: $matchedCount");
            $this->info("Tidak cocok/salah eja: " . count($unmatchedExcel));

            if (count($unmatchedExcel) > 0) {
                $this->warn("\nDaftar siswa yang tidak cocok:");
                foreach ($unmatchedExcel as $idx => $data) {
                    $item = $data['excel'];
                    $match = $data['best_match'];
                    $dist = $data['distance'];

                    $this->line(($idx + 1) . ". Sheet: {$item['sheet']}");
                    $this->line("   Excel: '{$item['nama']}' (NIS: {$item['nis']}, NISN: {$item['nisn']})");
                    if ($match) {
                        $this->line("   DB Terdekat: '{$match->nama_lengkap}' (ID: {$match->id}, NIS: {$match->nis}, NISN: {$match->nisn}, Kelas: " . ($match->kelas ? $match->kelas->nama : 'N/A') . ") [Jarak: $dist]");
                    } else {
                        $this->line("   DB Terdekat: Tidak ditemukan yang mirip");
                    }
                    $this->line("--------------------------------------------------------------------------------");
                }
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}

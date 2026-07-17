<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SyncSiswaKelasXi extends Command
{
    protected $signature = 'siswa:sync-kelas-xi {--dry-run : Menjalankan simulasi update tanpa menyimpan ke database}';
    protected $description = 'Menyesuaikan data siswa kelas XI dengan file Excel PENEMPATAN-KELAS-XI-2026-2027.xlsx';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $filePath = 'D:\\Project\\Website\\MAN 1 Kota Bandung\\Tahun Pelajaran 2026-2027\\PENEMPATAN-KELAS-XI-2026-2027.xlsx';

        if (!file_exists($filePath)) {
            $this->error("File Excel tidak ditemukan di: " . $filePath);
            return 1;
        }

        $this->info("Membaca file Excel dari: {$filePath}");
        if ($dryRun) {
            $this->warn("--- RUNNING IN DRY-RUN MODE (SIMULATION) ---");
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $excelRows = [];

            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                if (!str_starts_with($sheetName, "XI")) continue;
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $rows = $sheet->toArray();
                for ($i = 5; $i < count($rows); $i++) {
                    $nama = trim($rows[$i][1] ?? "");
                    if ($nama === "") continue;

                    $cName = trim($rows[$i][3] ?? '');
                    // Normalize "XI F1" to "XI.F-1"
                    $cNameDb = preg_replace('/^XI\s+F\s*(\d+)$/i', 'XI.F-$1', $cName);

                    $excelRows[] = [
                        'row' => $i + 1,
                        'sheet' => $sheetName,
                        'name' => $nama,
                        'class_xi_raw' => $cName,
                        'class_xi' => $cNameDb,
                        'jk' => trim($rows[$i][4] ?? ''),
                    ];
                }
            }

            $this->info("Berhasil membaca " . count($excelRows) . " data siswa dari Excel.");

            // Ambil TA Aktif/Ganjil 2026-2027 (ID: 1)
            $ta = TahunAkademik::find(1) 
                ?? TahunAkademik::where('nama', '2026-2027')->where('semester', 'ganjil')->first();
            if (!$ta) {
                $this->error("Tahun Akademik 2026-2027 Ganjil (ID: 1) tidak ditemukan.");
                return 1;
            }

            // Ambil semua kelas XI untuk TA 2026-2027 Ganjil
            $kelasXiList = Kelas::where('tahun_akademik_id', $ta->id)->where('nama', 'like', 'XI.%')->get();
            $kelasXiMap = $kelasXiList->pluck('id', 'nama')->toArray();

            // Cek apakah ada kelas XI di Excel yang tidak terdaftar di DB
            foreach ($excelRows as $row) {
                if (!isset($kelasXiMap[$row['class_xi']])) {
                    $this->error("Kelas '{$row['class_xi']}' (dari Excel '{$row['class_xi_raw']}') tidak ditemukan di database untuk TA 2026-2027.");
                    return 1;
                }
            }

            $dbSiswa = Siswa::all();
            $matched = [];
            $matchedDbIds = [];

            foreach ($excelRows as $row) {
                $excelNameNorm = $this->normalizeName($row['name']);
                $candidates = collect();

                // 1. Exact match
                $c = $dbSiswa->filter(function($s) use ($excelNameNorm) {
                    return $this->normalizeName($s->nama_lengkap) === $excelNameNorm;
                });
                foreach ($c as $s) $candidates->push($s);

                // 2. Substring match
                if ($candidates->isEmpty()) {
                    $c = $dbSiswa->filter(function($s) use ($excelNameNorm) {
                        $dbNorm = $this->normalizeName($s->nama_lengkap);
                        return stripos($dbNorm, $excelNameNorm) !== false || stripos($excelNameNorm, $dbNorm) !== false;
                    });
                    foreach ($c as $s) $candidates->push($s);
                }

                // 3. Levenshtein match
                if ($candidates->isEmpty()) {
                    $c = $dbSiswa->filter(function($s) use ($excelNameNorm) {
                        $dbNorm = $this->normalizeName($s->nama_lengkap);
                        return levenshtein($excelNameNorm, $dbNorm) <= 3;
                    });
                    foreach ($c as $s) $candidates->push($s);
                }

                // 4. Token-based word-by-word match
                if ($candidates->isEmpty()) {
                    $excelWords = explode(' ', $excelNameNorm);
                    $c = $dbSiswa->filter(function($s) use ($excelWords) {
                        $dbWords = explode(' ', $this->normalizeName($s->nama_lengkap));
                        if (count($excelWords) >= 2 && count($dbWords) >= 2) {
                            $w1Sim = levenshtein($excelWords[0], $dbWords[0]) <= 2;
                            $w2Sim = levenshtein($excelWords[1], $dbWords[1]) <= 2;
                            if ($w1Sim && $w2Sim) return true;
                            
                            if (count($excelWords) >= 3 && count($dbWords) >= 3) {
                                if ($excelWords[0] === $dbWords[0] && $excelWords[2] === $dbWords[2]) {
                                    return true;
                                }
                            }
                        }
                        return false;
                    });
                    foreach ($c as $s) $candidates->push($s);
                }

                $candidates = $candidates->unique('id');

                if ($candidates->count() === 1) {
                    $matched[] = [
                        'excel' => $row,
                        'db' => $candidates->first(),
                    ];
                    $matchedDbIds[] = $candidates->first()->id;
                } elseif ($candidates->count() > 1) {
                    $activeCandidates = $candidates->filter(fn($c) => $c->status === 'aktif');
                    if ($activeCandidates->count() === 1) {
                        $matched[] = [
                            'excel' => $row,
                            'db' => $activeCandidates->first(),
                        ];
                        $matchedDbIds[] = $activeCandidates->first()->id;
                    } else {
                        $this->error("Baris {$row['row']} (Sheet: {$row['sheet']}): '{$row['name']}' memiliki kandidat ambigu: " . $candidates->map(fn($c) => "[ID: {$c->id}] {$c->nama_lengkap}")->implode(', '));
                        return 1;
                    }
                } else {
                    $this->error("Baris {$row['row']} (Sheet: {$row['sheet']}): '{$row['name']}' tidak cocok dengan siswa manapun di database.");
                    return 1;
                }
            }

            // Cari siswa DB kelas XI (tahun_akademik_id = 1) yang saat ini aktif tapi TIDAK ada di Excel
            $dbSiswaXiActive = Siswa::where('status', 'aktif')
                ->whereIn('kelas_id', array_values($kelasXiMap))
                ->get();

            $toDeactivate = [];
            foreach ($dbSiswaXiActive as $s) {
                if (!in_array($s->id, $matchedDbIds)) {
                    $toDeactivate[] = $s;
                }
            }

            $this->info("\n=== PREVIEW SINKRONISASI KELAS XI ===");
            $this->info("1. Siswa yang akan DIUPDATE kelasnya ke XI (dan diaktifkan jika perlu): " . count($matched));
            $this->info("2. Siswa DB kelas XI aktif yang akan DINONAKTIFKAN (karena tidak ada di Excel): " . count($toDeactivate));

            if (count($toDeactivate) > 0) {
                $this->warn("\nDaftar 10 siswa yang akan dinonaktifkan:");
                foreach (array_slice($toDeactivate, 0, 10) as $idx => $s) {
                    $this->line(" - #" . ($idx + 1) . ": '{$s->nama_lengkap}' [ID: {$s->id}] (Kelas saat ini: {$s->kelas?->nama})");
                }
            }

            if ($dryRun) {
                $this->info("\nDry-run selesai. Tidak ada perubahan disimpan ke database.");
                return 0;
            }

            DB::beginTransaction();

            $updatedCount = 0;
            $deactivatedCount = 0;

            // 1. Update data siswa
            foreach ($matched as $m) {
                $siswa = $m['db'];
                $row = $m['excel'];
                $kelasId = $kelasXiMap[$row['class_xi']];

                $siswa->update([
                    'kelas_id' => $kelasId,
                    'tahun_akademik_id' => $ta->id,
                    'status' => 'aktif',
                ]);

                $updatedCount++;
            }

            // 2. Nonaktifkan yang tidak terdaftar
            foreach ($toDeactivate as $s) {
                $s->update(['status' => 'nonaktif']);
                $deactivatedCount++;
            }

            DB::commit();

            $this->info("\nSinkronisasi Kelas XI Sukses!");
            $this->info(" - Siswa Diupdate/Dipindahkan: " . $updatedCount);
            $this->info(" - Siswa Dinonaktifkan: " . $deactivatedCount);

            ActivityLog::record(
                'update',
                'siswa',
                "Sinkronisasi data kelas XI dari Excel: {$updatedCount} diupdate, {$deactivatedCount} dinonaktifkan",
                null,
                ['updated' => $updatedCount, 'deactivated' => $deactivatedCount]
            );

            return 0;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("\nTerjadi kesalahan fatal. Database di-rollback.");
            $this->error($e->getMessage());
            Log::error("SyncSiswaKelasXi Command Error: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return 1;
        }
    }

    private function normalizeName($name) {
        $name = strtolower(trim((string)$name));
        $name = str_replace(['.', '-', '\'', '`', '’'], ' ', $name);
        
        $words = explode(' ', $name);
        $cleanWords = [];
        foreach ($words as $word) {
            $word = trim($word);
            if ($word === '') continue;
            if ($word === 'm') {
                $cleanWords[] = 'muhammad';
            } elseif ($word === 'moch') {
                $cleanWords[] = 'mochamad';
            } else {
                $cleanWords[] = $word;
            }
        }
        
        return implode(' ', $cleanWords);
    }
}

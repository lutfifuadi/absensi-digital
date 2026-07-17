<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SyncKelasXSplit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siswa:sync-kelas-x-split {--dry-run : Menjalankan simulasi update tanpa menyimpan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memindahkan dan menyinkronkan data siswa kelas X dari kelas X.UMUM ke kelas spesifik (X-A s.d. X-L) sesuai file Excel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $filePath = 'D:\\Project\\Website\\MAN 1 Kota Bandung\\Tahun Pelajaran 2026-2027\\DAFTAR MURID KELAS X TAHUN AJARAN 2026-2027.xlsx';

        if (!file_exists($filePath)) {
            $this->error("File Excel tidak ditemukan di: " . $filePath);
            return Command::FAILURE;
        }

        $this->info("Membaca file Excel dari: {$filePath}");
        if ($dryRun) {
            $this->warn("--- RUNNING IN DRY-RUN MODE (SIMULATION) ---");
        }

        // Pemetaan sheet ke ID Kelas (Tahun Pelajaran 2026-2027, TA ID: 1)
        $mapping = [
            'X-A' => 44,
            'X-B' => 48,
            'X-C' => 49,
            'X-D' => 50,
            'X-E' => 51,
            'X-F' => 52,
            'X-G' => 53,
            'X-H' => 54,
            'X-I' => 55,
            'X-J (TKJ)' => 45,
            'X-K (TBSM)' => 46,
            'X-L (TABUS)' => 47,
        ];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $excelStudents = [];

            $normalize = function($str) {
                $str = strtolower(trim((string)$str));
                $str = preg_replace('/[^a-z0-9]/', '', $str);
                return $str;
            };

            foreach ($mapping as $sheetName => $kelasId) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if (!$sheet) {
                    $this->warn("Sheet '{$sheetName}' tidak ditemukan di file Excel!");
                    continue;
                }

                $rows = $sheet->toArray();
                
                // Cari index baris header ("NISN" atau "NIS" atau "NAMA") secara dinamis
                $headerIndex = -1;
                foreach ($rows as $idx => $row) {
                    $rowStr = json_encode($row);
                    if (stripos($rowStr, 'NISN') !== false || stripos($rowStr, 'NIS') !== false) {
                        $headerIndex = $idx;
                        break;
                    }
                }

                if ($headerIndex === -1) {
                    $this->warn("Header kolom tidak terdeteksi di sheet '{$sheetName}'. Menggunakan baris ke-7 (index 6) sebagai default.");
                    $headerIndex = 6;
                }

                // Looping dari baris setelah header
                for ($i = $headerIndex + 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    if (empty($row[0])) continue;

                    // Kolom 0 (P1): Nomor Urut
                    $noUrut = trim((string)$row[0]);
                    if (!is_numeric($noUrut)) continue;

                    // Ambil data kolom
                    // Kolom 2 (P3): NIS (biasanya index 3, tapi kita amankan)
                    $nis = isset($row[3]) ? trim((string)$row[3]) : '';
                    // Kolom 3 (P4): NISN (index 4)
                    $nisn = isset($row[4]) ? trim((string)$row[4]) : '';
                    // Kolom 4 (P5): Nama Lengkap (index 5)
                    $nama = isset($row[5]) ? trim((string)$row[5]) : '';

                    if (empty($nama)) continue;

                    $excelStudents[] = [
                        'sheet' => $sheetName,
                        'kelas_id' => $kelasId,
                        'no_urut' => $noUrut,
                        'nis' => $nis,
                        'nisn' => $nisn,
                        'nama' => $nama,
                        'normalized_nama' => $normalize($nama)
                    ];
                }
            }

            $totalExcel = count($excelStudents);
            $this->info("Berhasil membaca {$totalExcel} data siswa dari Excel.");

            // Ambil semua siswa di database yang saat ini berada di kelas X.UMUM (kelas_id = 43)
            $dbSiswa = Siswa::where('kelas_id', 43)->get();
            $this->info("Total siswa kelas X.UMUM di database: " . $dbSiswa->count());

            $matched = [];
            $unmatchedExcel = [];
            $matchedDbIds = [];
            $perKelasCount = array_fill_keys(array_values($mapping), 0);

            foreach ($excelStudents as $student) {
                $found = null;
                $studentNameNorm = $student['normalized_nama'];

                // Callback pencocokan nama fuzzy
                $nameMatchCallback = function($dbItem) use ($studentNameNorm, $normalize) {
                    $dbNorm = $normalize($dbItem->nama_lengkap);
                    return stripos($dbNorm, $studentNameNorm) !== false || stripos($studentNameNorm, $dbNorm) !== false;
                };

                // 1. Cari berdasarkan NISN DAN nama mirip
                if (!empty($student['nisn'])) {
                    $candidates = $dbSiswa->filter(fn($item) => ($item->nisn === $student['nisn'] || str_starts_with($item->nisn, $student['nisn'] . '-') || str_starts_with($student['nisn'], $item->nisn)));
                    if ($candidates->count() > 0) {
                        $found = $candidates->first($nameMatchCallback);
                    }
                }

                // 2. Cari berdasarkan NIS DAN nama mirip jika belum ketemu
                if (!$found && !empty($student['nis'])) {
                    $candidates = $dbSiswa->filter(fn($item) => $item->nis === $student['nis']);
                    if ($candidates->count() > 0) {
                        $found = $candidates->first($nameMatchCallback);
                    }
                }

                // 3. Cari berdasarkan Nama Lengkap (Normalized) persis jika belum ketemu
                if (!$found) {
                    $found = $dbSiswa->first(function($item) use ($studentNameNorm, $normalize) {
                        return $normalize($item->nama_lengkap) === $studentNameNorm;
                    });
                }

                // 4. Cari berdasarkan Nama Lengkap (Normalized) mirip jika belum ketemu
                if (!$found) {
                    $found = $dbSiswa->first($nameMatchCallback);
                }

                // 5. Fallback ke NISN saja (jika ada)
                if (!$found && !empty($student['nisn'])) {
                    $found = $dbSiswa->first(fn($item) => $item->nisn === $student['nisn'] || str_starts_with($item->nisn, $student['nisn'] . '-'));
                }

                // 6. Fallback ke NIS saja (jika ada)
                if (!$found && !empty($student['nis'])) {
                    $found = $dbSiswa->firstWhere('nis', $student['nis']);
                }

                if ($found) {
                    $matched[] = [
                        'excel' => $student,
                        'db' => $found
                    ];
                    $matchedDbIds[] = $found->id;
                    $perKelasCount[$student['kelas_id']]++;
                } else {
                    $unmatchedExcel[] = $student;
                }
            }

            // Hitung siswa di database kelas X.UMUM yang tersisa (tidak tercantum di Excel)
            $unmatchedDb = $dbSiswa->filter(function($item) use ($matchedDbIds) {
                return !in_array($item->id, $matchedDbIds);
            });

            // Mulai Proses Database Transaction
            if (!$dryRun) {
                DB::beginTransaction();
            }

            $successCount = 0;
            foreach ($matched as $match) {
                $dbSiswaItem = $match['db'];
                $kelasIdTujuan = $match['excel']['kelas_id'];

                if (!$dryRun) {
                    // Update kelas siswa di database
                    $dbSiswaItem->update([
                        'kelas_id' => $kelasIdTujuan,
                    ]);
                }
                $successCount++;
            }

            if (!$dryRun) {
                DB::commit();
                
                // Catat Log Aktivitas
                ActivityLog::record(
                    'update',
                    'siswa',
                    "Sinkronisasi pemindahan kelas X dari X.UMUM ke kelas spesifik. {$successCount} siswa dipindahkan.",
                    ['kelas_asal_id' => 43],
                    ['jumlah_dipindahkan' => $successCount]
                );
            }

            $this->info("\n=== RINGKASAN PROSES ===");
            $this->info("1. Total Siswa Excel Terbaca  : {$totalExcel}");
            $this->info("2. Siswa Berhasil Dipindahkan : {$successCount}");
            
            $this->newLine();
            $this->info("Detail Pemindahan per Kelas:");
            $kelasNamaMap = Kelas::whereIn('id', array_keys($perKelasCount))->pluck('nama', 'id')->toArray();
            foreach ($perKelasCount as $kelasId => $count) {
                $namaKelas = $kelasNamaMap[$kelasId] ?? "ID {$kelasId}";
                $this->line(" - Kelas {$namaKelas}: {$count} siswa");
            }

            $this->newLine();
            if (count($unmatchedExcel) > 0) {
                $this->warn("Daftar Siswa Excel yang TIDAK ditemukan di DB (" . count($unmatchedExcel) . "):");
                foreach ($unmatchedExcel as $idx => $student) {
                    $this->line(" - Sheet: {$student['sheet']}, Nama: {$student['nama']} (NISN: {$student['nisn']}, NIS: {$student['nis']})");
                }
            } else {
                $this->info("Semua siswa dari Excel tercocokkan dengan sukses di Database!");
            }

            $this->newLine();
            $this->info("Jumlah siswa kelas X.UMUM yang tersisa di database (tidak tercantum di Excel): " . $unmatchedDb->count());
            if ($unmatchedDb->count() > 0) {
                foreach ($unmatchedDb as $s) {
                    $this->line(" - Nama: {$s->nama_lengkap} (NISN: {$s->nisn}, NIS: {$s->nis}, Status: {$s->status})");
                }
            }

            // Otomatisasi nonaktifkan/hapus kelas X.UMUM jika kosong
            $this->newLine();
            $sisaSiswaXumumCount = Siswa::where('kelas_id', 43)->count();
            if (!$dryRun) {
                if ($sisaSiswaXumumCount === 0) {
                    $this->info("Kelas X.UMUM saat ini sudah kosong.");
                    if ($this->confirm("Apakah Anda ingin menghapus kelas X.UMUM dari database?", false)) {
                        $kelasXumum = Kelas::find(43);
                        if ($kelasXumum) {
                            $kelasXumum->delete();
                            $this->info("Kelas X.UMUM berhasil dihapus.");
                        }
                    }
                } else {
                    $this->warn("Kelas X.UMUM masih memiliki {$sisaSiswaXumumCount} siswa (aktif/nonaktif) di database, sehingga tidak dihapus.");
                }
            } else {
                if ($sisaSiswaXumumCount - $successCount === 0) {
                    $this->info("[Dry-run] Kelas X.UMUM disimulasikan kosong dan dapat dihapus.");
                } else {
                    $this->warn("[Dry-run] Kelas X.UMUM masih akan tersisa " . ($sisaSiswaXumumCount - $successCount) . " siswa.");
                }
            }

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            $this->error("\nTerjadi kesalahan fatal. Transaksi database dibatalkan.");
            $this->error($e->getMessage());
            Log::error("SyncKelasXSplit Command Error: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return Command::FAILURE;
        }
    }
}

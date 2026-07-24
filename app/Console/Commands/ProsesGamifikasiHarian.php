<?php

namespace App\Console\Commands;

use App\Models\AbsensiSiswa;
use App\Models\StudentGamificationStat;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ProsesGamifikasiHarian extends Command
{
    protected $signature = 'gamifikasi:proses-harian';

    protected $description = 'Proses harian poin gamifikasi siswa (early bird, streak, poin dasar)';

    public function handle(): int
    {
        $tanggal = now()->toDateString();

        $this->info("Memulai proses gamifikasi harian untuk tanggal: {$tanggal}");

        // Ambil semua absensi hari ini yang belum diproses observer (points_earned masih 0)
        $absensiHariIni = AbsensiSiswa::whereDate('tanggal', $tanggal)
            ->where('points_earned', 0)
            ->get();

        if ($absensiHariIni->isEmpty()) {
            $this->warn('Tidak ada data absensi untuk hari ini.');
            return self::SUCCESS;
        }

        // Group by kelas_id
        $perKelas = $absensiHariIni->groupBy('kelas_id');

        $totalDiproses = 0;

        foreach ($perKelas as $kelasId => $absensiKelas) {
            // Urutkan siswa hadir/terlambat berdasarkan jam_masuk ASC (null di akhir)
            $hadirTerlambat = $absensiKelas
                ->whereIn('status', ['hadir', 'terlambat'])
                ->filter(fn($a) => !is_null($a->jam_masuk))
                ->sortBy('jam_masuk')
                ->values();

            // Tentukan early bird per kelas: 5 pertama berdasarkan urutan jam_masuk
            $earlyBirdIds = $hadirTerlambat->take(5)->pluck('id')->toArray();

            // Proses setiap absensi dalam kelas ini
            foreach ($absensiKelas as $absensi) {
                $isEarlyBird = false;

                // Tandai early bird: masuk daftar 5 pertama ATAU jam_masuk <= 06:00
                if (
                    in_array($absensi->id, $earlyBirdIds) ||
                    ($absensi->jam_masuk && substr($absensi->jam_masuk, 0, 5) <= '06:00')
                ) {
                    $isEarlyBird = true;
                }

                // Ambil atau buat record gamifikasi siswa
                $stat = StudentGamificationStat::firstOrCreate(
                    ['siswa_id' => $absensi->siswa_id],
                    [
                        'current_streak'      => 0,
                        'longest_streak'      => 0,
                        'last_attendance_date' => null,
                    ]
                );

                // Hitung poin dasar berdasarkan status
                $poinDasar = match ($absensi->status) {
                    'hadir'     => 10,
                    'terlambat' => 5,
                    'sakit'     => 2,
                    'izin'      => 2,
                    'alpha'     => -10,
                    default     => 0,
                };

                // Bonus early bird
                $bonusEarlyBird = $isEarlyBird ? 5 : 0;

                // Update streak terlebih dahulu agar bisa cek bonus streak
                if (in_array($absensi->status, ['hadir', 'terlambat'])) {
                    $stat->current_streak      += 1;
                    $stat->last_attendance_date = $tanggal;

                    if ($stat->current_streak > $stat->longest_streak) {
                        $stat->longest_streak = $stat->current_streak;
                    }
                } else {
                    // Alpha / sakit / izin → reset streak
                    $stat->current_streak = 0;
                }

                // Bonus streak: jika current_streak >= 5, tambah +5
                $bonusStreak = ($stat->current_streak >= 5) ? 5 : 0;

                $totalPoin = $poinDasar + $bonusEarlyBird + $bonusStreak;

                // Simpan perubahan stat gamifikasi
                $stat->save();

                // Update kolom di absensi_siswa
                $absensi->points_earned = $totalPoin;
                $absensi->is_early_bird = $isEarlyBird;
                $absensi->saveQuietly(); // saveQuietly agar tidak mentrigger observer

                $totalDiproses++;
            }
        }

        $this->info("Proses gamifikasi selesai. Total siswa diproses: {$totalDiproses}");

        return self::SUCCESS;
    }
}

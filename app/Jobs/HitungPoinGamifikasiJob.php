<?php

namespace App\Jobs;

use App\Models\AbsensiSiswa;
use App\Models\StudentGamificationStat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HitungPoinGamifikasiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public AbsensiSiswa $absensi)
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $absensi = $this->absensi;

        $statusLower = strtolower($absensi->status);
        $poin = 0;
        $isEarlyBird = false;

        // 1. Poin Dasar
        match($statusLower) {
            'hadir'     => $poin = 10,
            'terlambat' => $poin = 5,
            'sakit', 'izin' => $poin = 2,
            'alpha'     => $poin = -10,
            default     => $poin = 0,
        };

        // 2. Early Bird: jam masuk >= 04:00 dan <= 06:00
        if (in_array($statusLower, ['hadir', 'terlambat'])) {
            $jamMasuk = !empty($absensi->jam_masuk) ? substr($absensi->jam_masuk, 0, 5) : null;
            if (!empty($jamMasuk) && $jamMasuk >= '04:00' && $jamMasuk <= '06:00') {
                $poin += 5;
                $isEarlyBird = true;
            }
        }

        // 3. Streak
        $stat = StudentGamificationStat::firstOrCreate(
            ['siswa_id' => $absensi->siswa_id],
            [
                'current_streak'       => 0,
                'longest_streak'       => 0,
                'last_attendance_date' => null,
            ]
        );

        if (in_array($statusLower, ['hadir', 'terlambat'])) {
            $stat->current_streak += 1;
            if ($stat->current_streak > $stat->longest_streak) {
                $stat->longest_streak = $stat->current_streak;
            }
            if ($stat->current_streak >= 5) {
                $poin += 5;
            }
            $stat->last_attendance_date = now()->toDateString(); // hanya update saat hadir
        } else {
            $stat->current_streak = 0;
        }

        $stat->save();

        // 4. Simpan poin ke absensi secara quiet
        $absensi->updateQuietly([
            'points_earned' => $poin,
            'is_early_bird' => $isEarlyBird,
        ]);
    }
}

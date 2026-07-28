<?php

namespace App\Jobs;

use App\Models\JadwalPelajaran;
use App\Models\MonitoringKehadiranGuru;
use App\Models\MonitoringNotifikasiLog;
use App\Models\User;
use App\Notifications\BelumMonitorNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class BelumMonitorNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $today = Carbon::today();
        $now = Carbon::now();
        $hariIni = $this->getIndonesianDayName($today->dayOfWeek);
        $timeString = $now->format('H:i');

        // Ambil semua jadwal hari ini
        $jadwals = JadwalPelajaran::with(['kelas', 'guru'])
            ->where('hari', $hariIni)
            ->get();

        if ($jadwals->isEmpty()) {
            return;
        }

        // Ambil Waka Kurikulum
        $wakas = User::withRole(User::ROLE_WAKA_KURIKULUM)->get();
        if ($wakas->isEmpty()) {
            return;
        }

        foreach ($jadwals as $jadwal) {
            $jamMulai = Carbon::createFromFormat('H:i:s', $jadwal->jam_mulai);
            
            // Cek apakah sudah lewat 15 menit dari jam mulai
            if ($now->diffInMinutes($jamMulai, false) < -15) {
                // Cek apakah sudah dimonitor
                $isMonitored = MonitoringKehadiranGuru::where('jadwal_pelajaran_id', $jadwal->id)
                    ->whereDate('tanggal', $today)
                    ->exists();

                if (!$isMonitored) {
                    // Cek apakah sudah dinotifikasi
                    $isNotified = MonitoringNotifikasiLog::where('jadwal_pelajaran_id', $jadwal->id)
                        ->whereDate('tanggal', $today)
                        ->where('tipe', 'belum_monitor_15mnt')
                        ->exists();

                    if (!$isNotified) {
                        // Kirim Notifikasi
                        Notification::send($wakas, new BelumMonitorNotification($jadwal));

                        // Catat log
                        MonitoringNotifikasiLog::create([
                            'jadwal_pelajaran_id' => $jadwal->id,
                            'tanggal' => $today,
                            'tipe' => 'belum_monitor_15mnt',
                        ]);
                    }
                }
            }
        }
    }

    private function getIndonesianDayName(int $dayOfWeek): string
    {
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        return $days[$dayOfWeek] ?? '';
    }
}

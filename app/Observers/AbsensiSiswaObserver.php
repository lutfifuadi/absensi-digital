<?php

namespace App\Observers;

use App\Models\AbsensiSiswa;
use App\Models\Pengaturan;
use App\Jobs\SendWhatsAppMessage;

class AbsensiSiswaObserver
{
    /**
     * Handle the AbsensiSiswa "created" event.
     */
    public function created(AbsensiSiswa $absensiSiswa): void
    {
        $this->kirimNotifikasiKeOrtu($absensiSiswa);
        $this->hitungPoinGamifikasi($absensiSiswa);
    }

    /**
     * Handle the AbsensiSiswa "updated" event.
     */
    public function updated(AbsensiSiswa $absensiSiswa): void
    {
        // jika status/jam pulang berubah? Opsional kita bisa kirim WA kepulangan.
        // Untuk sekarang, pastikan jika jam pulang terisi dan ada isDirty('jam_pulang')
        if ($absensiSiswa->isDirty('jam_pulang') && !empty($absensiSiswa->jam_pulang)) {
            $this->kirimNotifikasiKeOrtu($absensiSiswa, 'pulang');
        }
    }

    private function kirimNotifikasiKeOrtu(AbsensiSiswa $absensi, string $tipe = 'masuk'): void
    {
        // 1. Cek apakah WA aktif di pengaturan
        $platform = Pengaturan::where('key', 'jenis_notifikasi_ortu')->value('value');
        if ($platform !== 'WhatsApp (WA)') {
            return;
        }

        // 1b. Cek toggle on/off WA Gateway
        $waEnabled = Pengaturan::where('key', 'wa_gateway_enabled')->value('value');
        if ($waEnabled === 'Tidak') {
            return;
        }

        // 2. Cek apakah siswa punya nomor HP / nomor ortu
        $absensi->loadMissing('siswa', 'siswa.kelas');
        $siswa = $absensi->siswa;
        
        $nomorTujuan = $siswa->no_hp_ortu ?: $siswa->no_hp;
        if (empty($nomorTujuan)) {
            return;
        }

        // Ambil info lembaga
        $namaLembaga = Pengaturan::where('key', 'nama_lembaga')->value('value') ?: 'Sekolah';
        
        // 3. Ambil Template Redaksi dari Database
        $templateType = $tipe === 'pulang' ? 'pulang' : strtolower($absensi->status) . '_masuk';
        $template = \App\Models\NotificationTemplate::where('type', $templateType)->first();

        $tanggal = \Carbon\Carbon::parse($absensi->tanggal)->translatedFormat('d F Y');
        $hari = \Carbon\Carbon::parse($absensi->tanggal)->translatedFormat('l');
        $waktu = $tipe === 'masuk' ? $absensi->jam_masuk : $absensi->jam_pulang;
        if (!$waktu) $waktu = '-';
        $jam = $waktu ? \Carbon\Carbon::parse($waktu)->format('H:i') : '-';

        $namaKelas = $siswa->kelas ? $siswa->kelas->nama : '-';

        $statusEmoji = [
            'hadir' => '✅',
            'sakit' => '🤒',
            'izin' => '📝',
            'alpha' => '❌',
            'terlambat' => '⚠️'
        ];

        $emoji = $statusEmoji[strtolower($absensi->status)] ?? '✅';
        $statusLabel = strtoupper($absensi->status);

        if ($template) {
            // Gunakan template dari database
            $pesan = str_replace([
                '{nama}', '{tanggal}', '{waktu}', '{status}', '{lembaga}', '{keterangan}', '{kelas}', '{hari}', '{jam}'
            ], [
                $siswa->nama_lengkap, $tanggal, $waktu, $statusLabel, $namaLembaga, $absensi->keterangan ?: '-', $namaKelas, $hari, $jam
            ], $template->content);
        } else {
            // Fallback ke pesan default jika template tidak ditemukan
            $pesan = "*INFO ABSENSI {$namaLembaga}*\n\n";
            $pesan .= "Yth. Orang Tua / Wali dari:\n";
            $pesan .= "👤 *Nama*: {$siswa->nama_lengkap}\n";
            
            if ($tipe === 'masuk') {
                $pesan .= "Membagikan informasi kehadiran untuk:\n";
                $pesan .= "📅 *Tanggal*: {$tanggal}\n";
                $pesan .= "⏰ *Jam Masuk*: {$waktu}\n";
                $pesan .= "📊 *Status*: {$emoji} {$statusLabel}\n\n";
                if ($absensi->keterangan) {
                    $pesan .= "📝 *Keterangan*: {$absensi->keterangan}\n\n";
                }
            } else {
                $pesan .= "Membagikan informasi kepulangan untuk:\n";
                $pesan .= "📅 *Tanggal*: {$tanggal}\n";
                $pesan .= "⏰ *Jam Kepulangan*: {$waktu}\n\n";
                $pesan .= "Semoga selamat sampai di rumah.\n\n";
            }
        }

        // 4. Dispatch Job WA — dengan validasi nomor & queue 'notifications'
        $delaySecs = (int)(Pengaturan::where('key', 'jeda_waktu_kirim_notifikasi_detik')->value('value') ?: 1);
        
        SendWhatsAppMessage::dispatch(
            $nomorTujuan, 
            $pesan, 
            'Pesan Otomatis - Jangan Dibalas',
            true,          // validateNumber=true: cek dulu ke API sebelum kirim
            $siswa->id
        )->delay(now()->addSeconds($delaySecs));
    }

    private function hitungPoinGamifikasi(AbsensiSiswa $absensi): void
    {
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

        // 2. Early Bird: jam masuk <= 06:00
        if (in_array($statusLower, ['hadir', 'terlambat'])) {
            $jamMasuk = $absensi->jam_masuk ? substr($absensi->jam_masuk, 0, 5) : null;
            if ($jamMasuk && $jamMasuk <= '06:00') {
                $poin += 5;
                $isEarlyBird = true;
            }
        }

        // 3. Streak
        $stat = \App\Models\StudentGamificationStat::firstOrCreate(
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

        // 4. Simpan poin ke absensi
        \App\Models\AbsensiSiswa::where('id', $absensi->id)->update([
            'points_earned' => $poin,
            'is_early_bird' => $isEarlyBird,
        ]);
    }
}

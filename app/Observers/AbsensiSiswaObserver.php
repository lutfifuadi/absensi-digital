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
}

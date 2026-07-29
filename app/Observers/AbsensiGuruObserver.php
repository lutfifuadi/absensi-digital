<?php

namespace App\Observers;

use App\Models\AbsensiGuru;
use App\Models\Pengaturan;
use App\Models\NotificationTemplate;
use App\Jobs\SendWhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AbsensiGuruObserver
{
    /**
     * Handle the AbsensiGuru "created" event.
     */
    public function created(AbsensiGuru $absensiGuru): void
    {
        $this->kirimNotifikasiKeGuru($absensiGuru, 'masuk');
    }

    /**
     * Handle the AbsensiGuru "updated" event.
     */
    public function updated(AbsensiGuru $absensiGuru): void
    {
        if ($absensiGuru->isDirty('jam_pulang') && !empty($absensiGuru->jam_pulang)) {
            $this->kirimNotifikasiKeGuru($absensiGuru, 'pulang');
        }
    }

    private function kirimNotifikasiKeGuru(AbsensiGuru $absensi, string $tipe = 'masuk'): void
    {
        try {
            // 1. Cek toggle on/off WA Gateway
            $waEnabled = Pengaturan::where('key', 'wa_gateway_enabled')->value('value');
            if ($waEnabled === 'Tidak') {
                return;
            }

            // 2. Load relasi guru & cek nomor HP guru
            $absensi->loadMissing('guru');
            $guru = $absensi->guru;

            if (!$guru || empty($guru->no_hp)) {
                return;
            }

            $nomorTujuan = $guru->no_hp;

            // Info lembaga
            $namaLembaga = Pengaturan::where('key', 'nama_sekolah')->value('value')
                ?: (Pengaturan::where('key', 'nama_lembaga')->value('value') ?: 'Sekolah');

            // 3. Ambil Template Redaksi dari Database
            $templateType = $tipe === 'pulang' ? 'guru_pulang' : 'guru_' . strtolower($absensi->status) . '_masuk';
            $template = NotificationTemplate::where('type', $templateType)->first();

            $tanggalObj = Carbon::parse($absensi->tanggal);
            $tanggal = $tanggalObj->locale('id')->translatedFormat('d F Y');
            $hari = $tanggalObj->locale('id')->isoFormat('dddd');

            $waktu = $tipe === 'masuk' ? $absensi->jam_masuk : $absensi->jam_pulang;
            if ($waktu === null || $waktu === '' || $waktu === '-') {
                $jam = '-';
            } else {
                $jam = Carbon::parse($waktu)->format('H:i');
            }

            $statusEmoji = [
                'hadir' => '✅',
                'sakit' => '🤒',
                'izin' => '📝',
                'alpha' => '❌',
                'terlambat' => '⚠️'
            ];

            $statusLower = strtolower($absensi->status);
            $emoji = $statusEmoji[$statusLower] ?? '✅';
            $statusLabel = strtoupper($absensi->status);
            $nip = $guru->nip ?: '-';
            $jabatan = $guru->jabatan ?: '-';

            if ($template) {
                // Gunakan template dari database
                $pesan = str_replace([
                    '{nama}', '{nip}', '{tanggal}', '{waktu}', '{status}', '{lembaga}', '{keterangan}', '{hari}', '{jam}', '{jabatan}'
                ], [
                    $guru->nama_lengkap, $nip, $tanggal, $waktu, $statusLabel, $namaLembaga, $absensi->keterangan ?: '-', $hari, $jam, $jabatan
                ], $template->content);
            } else {
                // Fallback jika template belum diset
                $pesan = "*PRESENSI GURU {$namaLembaga}*\n\n";
                $pesan .= "Yth. Bapak/Ibu *{$guru->nama_lengkap}* ({$nip})\n\n";

                if ($tipe === 'masuk') {
                    $pesan .= "📅 *Tanggal*: {$tanggal}\n";
                    $pesan .= "⏰ *Jam Datang*: {$jam} WIB\n";
                    $pesan .= "📊 *Status*: {$emoji} {$statusLabel}\n\n";
                    if ($absensi->keterangan) {
                        $pesan .= "📝 *Keterangan*: {$absensi->keterangan}\n\n";
                    }
                } else {
                    $pesan .= "📅 *Tanggal*: {$tanggal}\n";
                    $pesan .= "⏰ *Jam Kepulangan*: {$jam} WIB\n\n";
                    $pesan .= "Terima kasih dan selamat beristirahat.\n\n";
                }
            }

            // 4. Dispatch Job WA
            $delaySecs = (int)(Pengaturan::where('key', 'jeda_waktu_kirim_notifikasi_detik')->value('value') ?: 1);

            SendWhatsAppMessage::dispatch(
                $nomorTujuan,
                $pesan,
                'Pesan Otomatis - Presensi Guru',
                true,
                $guru->id
            )->delay(now()->addSeconds($delaySecs));

        } catch (\Exception $e) {
            Log::error("Gagal mengirim notifikasi WA presensi guru ID {$absensi->id}: " . $e->getMessage());
        }
    }
}

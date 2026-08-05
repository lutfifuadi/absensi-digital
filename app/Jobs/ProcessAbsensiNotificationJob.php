<?php

namespace App\Jobs;

use App\Models\AbsensiSiswa;
use App\Models\NotificationTemplate;
use App\Models\Pengaturan;
use App\Notifications\NotifikasiAutoAlpha;
use App\Services\PengaturanService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessAbsensiNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public AbsensiSiswa $absensiSiswa,
        public string $event = 'created'
    ) {
        $this->onQueue('notifications');
    }

    public function handle(PengaturanService $pengaturanService): void
    {
        $absensi = $this->absensiSiswa;

        if ($this->event === 'created') {
            // ── Kirim notifikasi in-app ke orang tua (selalu) ────────────────
            $this->kirimNotifInAppKeOrtu($absensi);

            // ── Kirim WA ke orang tua ────────────────────────────────────────
            // Jika auto-alpha, cek toggle auto_alpha_wa_notif dulu
            $isAutoAlpha = strtolower($absensi->metode ?? '') === 'auto-alpha';

            if ($isAutoAlpha) {
                if (!$pengaturanService->isAutoAlphaWaNotifEnabled()) {
                    // Toggle WA auto-alpha dinonaktifkan, skip kirim WA
                    return;
                }
            }

            $tipe = (empty($absensi->jam_masuk) && !empty($absensi->jam_pulang)) ? 'pulang' : 'masuk';
            $this->kirimNotifikasiKeOrtu($absensi, $tipe);
        } elseif ($this->event === 'updated') {
            if ($absensi->isDirty('jam_pulang') && !empty($absensi->jam_pulang)) {
                $this->kirimNotifikasiKeOrtu($absensi, 'pulang');
            }
        }
    }

    /**
     * Kirim notifikasi in-app ke orang tua via Laravel Database Notification.
     */
    private function kirimNotifInAppKeOrtu(AbsensiSiswa $absensi): void
    {
        try {
            $absensi->loadMissing('siswa', 'siswa.kelas', 'siswa.ortuUser');
            $siswa = $absensi->siswa;

            $ortuUser = $siswa?->ortuUser;
            if (!$ortuUser) {
                return;
            }

            $namaSiswa = $siswa->nama_lengkap ?? '-';
            $namaKelas = $siswa->kelas?->nama ?? '-';
            $tanggal   = $absensi->tanggal instanceof Carbon
                ? $absensi->tanggal->toDateString()
                : $absensi->tanggal;

            $ortuUser->notify(
                new NotifikasiAutoAlpha(
                    'ortu',
                    $namaSiswa,
                    $namaKelas,
                    $tanggal,
                    '-',
                    $absensi->keterangan ?? '-'
                )
            );
        } catch (\Exception $e) {
            Log::error(
                "Gagal mengirim notifikasi in-app auto-alpha ke ortu: " . $e->getMessage()
            );
        }
    }

    private function kirimNotifikasiKeOrtu(AbsensiSiswa $absensi, string $tipe = 'masuk'): void
    {
        // 1. Cek apakah WA aktif di pengaturan
        $platform = setting('jenis_notifikasi_ortu');
        if ($platform !== 'WhatsApp (WA)') {
            return;
        }

        // 1b. Cek toggle on/off WA Gateway
        if (!feature('wa_gateway_enabled')) {
            return;
        }

        // 2. Cek apakah siswa punya nomor HP / nomor ortu
        $absensi->loadMissing('siswa', 'siswa.kelas');
        $siswa = $absensi->siswa;

        if (!$siswa) {
            return;
        }

        $nomorTujuan = $siswa->no_hp_ortu ?: $siswa->no_hp;
        if (empty($nomorTujuan)) {
            return;
        }

        // Ambil info lembaga
        $namaLembaga = setting('nama_lembaga') ?: setting('nama_sekolah') ?: 'Sekolah';

        // 3. Ambil Template Redaksi dari Database
        $templateType = $tipe === 'pulang' ? 'pulang' : strtolower($absensi->status) . '_masuk';
        $template = NotificationTemplate::where('type', $templateType)->first();

        $tanggal = Carbon::parse($absensi->tanggal)->locale('id')->translatedFormat('d F Y');
        $hari = Carbon::parse($absensi->tanggal)->locale('id')->isoFormat('dddd');
        $waktu = $tipe === 'masuk' ? $absensi->jam_masuk : $absensi->jam_pulang;
        if (!$waktu) $waktu = '-';

        // BUG-002: Handle null waktu sebelum Carbon::parse
        if ($waktu === null || $waktu === '-') {
            $jam = '-';
        } else {
            $jam = Carbon::parse($waktu)->format('H:i');
        }

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
                if (empty($absensi->jam_masuk)) {
                    $pesan .= "⚠️ *Peringatan*: Siswa tercatat tidak melakukan presensi masuk di pagi hari.\n\n";
                }
                $pesan .= "Semoga selamat sampai di rumah.\n\n";
            }
        }

        // 4. Dispatch Job WA — dengan validasi nomor & queue 'notifications'
        $delaySecs = (int)(Cache::remember('setting_jeda_notifikasi_wa', 300, function () {
            return Pengaturan::where('key', 'jeda_waktu_kirim_notifikasi_detik')->value('value') ?: 1;
        }));

        SendWhatsAppMessage::dispatch(
            $nomorTujuan,
            $pesan,
            'Pesan Otomatis - Jangan Dibalas',
            true,          // validateNumber=true: cek dulu ke API sebelum kirim
            $siswa->id
        )->delay(now()->addSeconds($delaySecs));
    }
}

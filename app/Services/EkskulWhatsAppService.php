<?php

namespace App\Services;

use App\Models\Ekskul;
use App\Models\Siswa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class EkskulWhatsAppService
{
    public function __construct(
        private WhatsAppService $waService
    ) {}

    /**
     * Kirim notifikasi alpha ke orang tua siswa.
     *
     * Format pesan:
     * "Yth. Orang Tua [Nama Siswa], diberitahukan bahwa putra/putri Anda tercatat
     *  TIDAK HADIR pada kegiatan ekstrakurikuler [Nama Ekskul] hari [Tanggal].
     *  Mohon konfirmasi ke wali kelas. — MAN 1 Kota Bandung"
     *
     * @param  int     $siswaId
     * @param  int     $ekskulId
     * @param  string  $tanggal    Format: Y-m-d
     * @return bool
     */
    public function notifyAlpha(int $siswaId, int $ekskulId, string $tanggal): bool
    {
        // Cek apakah WA gateway aktif
        if (!$this->waService->isEnabled()) {
            Log::info("EkskulWhatsApp: WA gateway tidak aktif, notifikasi alpha dilewati.");
            return false;
        }

        $siswa  = Siswa::find($siswaId);
        $ekskul = Ekskul::find($ekskulId);

        if (!$siswa || !$ekskul) {
            Log::warning("EkskulWhatsApp: Siswa ID {$siswaId} atau Ekskul ID {$ekskulId} tidak ditemukan.");
            return false;
        }

        // Cek nomor WhatsApp orang tua tersedia
        $noHpOrtu = $siswa->no_hp_ortu;
        if (empty($noHpOrtu)) {
            Log::info("EkskulWhatsApp: Siswa {$siswa->nama_lengkap} (ID {$siswaId}) tidak memiliki nomor HP orang tua.");
            return false;
        }

        // Format tanggal ke Bahasa Indonesia
        $tanggalFormatted = Carbon::parse($tanggal)
            ->locale('id')
            ->translatedFormat('l, d F Y');

        // Susun pesan notifikasi
        $message = "Yth. Orang Tua {$siswa->nama_lengkap}, diberitahukan bahwa putra/putri Anda tercatat TIDAK HADIR pada kegiatan ekstrakurikuler {$ekskul->nama} hari {$tanggalFormatted}. Mohon konfirmasi ke wali kelas. — MAN 1 Kota Bandung";

        // Kirim via WhatsAppService (validasi nomor + kirim)
        $sent = $this->waService->sendMessageIfValid(
            number: $noHpOrtu,
            message: $message,
            footer: '',
            siswaId: $siswaId
        );

        if ($sent) {
            Log::info("EkskulWhatsApp: Notifikasi alpha terkirim ke ortu {$siswa->nama_lengkap} ({$noHpOrtu}).");
        } else {
            Log::warning("EkskulWhatsApp: Gagal mengirim notifikasi alpha ke ortu {$siswa->nama_lengkap} ({$noHpOrtu}).");
        }

        return $sent;
    }

    /**
     * Kirim ringkasan kehadiran bulanan ke orang tua (opsional).
     *
     * @param  int     $siswaId
     * @param  int     $ekskulId
     * @param  array   $rekapData   Data rekap dari EkskulAbsensiService::getRekapPerSiswa()
     * @param  int     $bulan       1-12
     * @param  int     $tahun
     * @return bool
     */
    public function notifyRingkasanBulanan(
        int $siswaId,
        int $ekskulId,
        array $rekapData,
        int $bulan,
        int $tahun
    ): bool {
        if (!$this->waService->isEnabled()) {
            Log::info("EkskulWhatsApp: WA gateway tidak aktif, ringkasan bulanan dilewati.");
            return false;
        }

        $siswa  = Siswa::find($siswaId);
        $ekskul = Ekskul::find($ekskulId);

        if (!$siswa || !$ekskul) {
            Log::warning("EkskulWhatsApp: Siswa ID {$siswaId} atau Ekskul ID {$ekskulId} tidak ditemukan.");
            return false;
        }

        $noHpOrtu = $siswa->no_hp_ortu;
        if (empty($noHpOrtu)) {
            Log::info("EkskulWhatsApp: Siswa {$siswa->nama_lengkap} tidak memiliki nomor HP orang tua.");
            return false;
        }

        // Nama bulan dalam Bahasa Indonesia
        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)
            ->locale('id')
            ->translatedFormat('F Y');

        $total = $rekapData['total'] ?? [];
        $hadir    = $total['hadir'] ?? 0;
        $izin     = $total['izin'] ?? 0;
        $sakit    = $total['sakit'] ?? 0;
        $alpha    = $total['alpha'] ?? 0;
        $terlambat = $total['terlambat'] ?? 0;
        $totalPertemuan = $total['total'] ?? 0;

        $message = "Yth. Orang Tua {$siswa->nama_lengkap},\n\n"
            . "Berikut ringkasan kehadiran putra/putri Anda pada kegiatan ekstrakurikuler *{$ekskul->nama}* bulan *{$namaBulan}*:\n\n"
            . "📊 Total Pertemuan: {$totalPertemuan}\n"
            . "✅ Hadir: {$hadir}\n"
            . "📝 Izin: {$izin}\n"
            . "🏥 Sakit: {$sakit}\n"
            . "❌ Alpha: {$alpha}\n"
            . "⏰ Terlambat: {$terlambat}\n\n"
            . "Mohon bimbingan dan perhatiannya. Terima kasih.\n"
            . "— MAN 1 Kota Bandung";

        $sent = $this->waService->sendMessageIfValid(
            number: $noHpOrtu,
            message: $message,
            footer: '',
            siswaId: $siswaId
        );

        if ($sent) {
            Log::info("EkskulWhatsApp: Ringkasan bulanan terkirim ke ortu {$siswa->nama_lengkap}.");
        } else {
            Log::warning("EkskulWhatsApp: Gagal mengirim ringkasan bulanan ke ortu {$siswa->nama_lengkap}.");
        }

        return $sent;
    }
}

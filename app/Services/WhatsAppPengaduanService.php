<?php

namespace App\Services;

use App\Models\Pengaturan;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppPengaduanService
{
    protected string $apiKey;
    protected string $endpoint;
    protected string $sender;
    protected string $groupId;

    public function __construct()
    {
        $this->apiKey   = setting('wa_pengaduan_api_key', '');
        $this->endpoint = setting('wa_pengaduan_endpoint', 'https://wa.lutfifuadi.my.id');
        $this->sender   = setting('wa_pengaduan_sender', '');
        $this->groupId  = setting('wa_pengaduan_group_id', '');
    }

    /**
     * Kirim kode unik ke nomor pelapor.
     */
    public function sendKodeUnik(string $nomorWa, string $kodeUnik, string $nama, string $kelas = '-'): bool
    {
        $namaKelas = !empty($kelas) && $kelas !== '-' ? $kelas : '-';
        $namaLembaga = setting('nama_lembaga') ?: setting('nama_sekolah') ?: 'Sekolah';

        $template = NotificationTemplate::where('type', 'pengaduan_kode_unik')->value('content');
        if (empty($template)) {
            $message = "Halo *{$nama}*,\n\n"
                . "Terima kasih telah melaporkan data tidak valid.\n\n"
                . "Berikut kode unik pengaduan Anda:\n"
                . "*{$kodeUnik}*\n\n"
                . "Simpan kode ini untuk mengecek status pengaduan Anda.\n\n"
                . "Sistem Pengaduan Data - {$namaLembaga}";
        } else {
            $search = [
                '{nama}', '{nama_lengkap}', '(nama)', '(nama_lengkap)',
                '{kode_unik}', '{kode}', '(kode_unik)', '(kode)',
                '{kelas}', '{kelas_nama}', '(kelas)', '(kelas_nama)', '{class}', '(class)'
            ];
            $replace = [
                $nama, $nama, $nama, $nama,
                $kodeUnik, $kodeUnik, $kodeUnik, $kodeUnik,
                $namaKelas, $namaKelas, $namaKelas, $namaKelas, $namaKelas, $namaKelas
            ];
            $message = str_replace($search, $replace, $template);
        }

        return $this->sendMessage($nomorWa, $message);
    }

    /**
     * Kirim update status ke pelapor.
     */
    public function sendStatusUpdate(string $nomorWa, string $kodeUnik, string $status, string $catatan = '', string $nama = '', string $kelas = '-'): bool
    {
        $statusLabel = match ($status) {
            'diproses' => 'Diproses',
            'selesai'  => 'Selesai',
            'ditolak'  => 'Ditolak',
            default    => ucfirst($status),
        };
        $namaKelas = !empty($kelas) && $kelas !== '-' ? $kelas : '-';
        $namaLembaga = setting('nama_lembaga') ?: setting('nama_sekolah') ?: 'Sekolah';

        $template = NotificationTemplate::where('type', 'pengaduan_status_update')->value('content');
        if (empty($template)) {
            $message = "Halo,\n\n"
                . "Pengaduan dengan kode *{$kodeUnik}* telah diupdate.\n\n"
                . "Status: *{$statusLabel}*\n";

            if ($catatan) {
                $message .= "Catatan: {$catatan}\n";
            }

            $message .= "\nTerima kasih telah menggunakan layanan pengaduan kami.\n\n"
                . "Sistem Pengaduan Data - {$namaLembaga}";
        } else {
            $catatanText = $catatan ? "Catatan: {$catatan}\n" : '';
            $search = [
                '{kode_unik}', '{kode}', '(kode_unik)', '(kode)',
                '{status}', '(status)',
                '{catatan}', '{catatan_admin}', '(catatan)', '(catatan_admin)',
                '{nama}', '{nama_lengkap}', '(nama)', '(nama_lengkap)',
                '{kelas}', '{kelas_nama}', '(kelas)', '(kelas_nama)', '{class}', '(class)'
            ];
            $replace = [
                $kodeUnik, $kodeUnik, $kodeUnik, $kodeUnik,
                $statusLabel, $statusLabel,
                $catatanText, $catatanText, $catatanText, $catatanText,
                $nama, $nama, $nama, $nama,
                $namaKelas, $namaKelas, $namaKelas, $namaKelas, $namaKelas, $namaKelas
            ];
            $message = str_replace($search, $replace, $template);
        }

        return $this->sendMessage($nomorWa, $message);
    }

    /**
     * Kirim notifikasi ke grup admin.
     */
    public function sendToGroupAdmin(string $kodeUnik, string $nama, string $statusPelapor, string $kategori, string $deskripsi, string $kelas = '-'): bool
    {
        $statusPelaporLabel = $statusPelapor === 'siswa' ? 'Siswa' : 'Orang Tua';
        $namaKelas = !empty($kelas) && $kelas !== '-' ? $kelas : '-';
        $namaLembaga = setting('nama_lembaga') ?: setting('nama_sekolah') ?: 'Sekolah';

        $template = NotificationTemplate::where('type', 'pengaduan_group_admin')->value('content');
        if (empty($template)) {
            $message = "━━━ *PENGADUAN BARU* ━━━\n\n"
                . "Kode: *{$kodeUnik}*\n"
                . "Nama: {$nama}\n"
                . "Kelas: {$namaKelas}\n"
                . "Status: {$statusPelaporLabel}\n"
                . "Kategori: {$kategori}\n\n"
                . "Deskripsi:\n{$deskripsi}\n\n"
                . "Silakan proses pengaduan ini di panel admin.\n"
                . "Sistem Pengaduan Data - {$namaLembaga}";
        } else {
            $search = [
                '{kode_unik}', '{kode}', '(kode_unik)', '(kode)',
                '{nama}', '{nama_lengkap}', '(nama)', '(nama_lengkap)',
                '{kelas}', '{kelas_nama}', '(kelas)', '(kelas_nama)', '{class}', '(class)',
                '{status}', '{status_pelapor}', '(status)', '(status_pelapor)',
                '{kategori}', '(kategori)',
                '{deskripsi}', '{pesan}', '(deskripsi)', '(pesan)'
            ];
            $replace = [
                $kodeUnik, $kodeUnik, $kodeUnik, $kodeUnik,
                $nama, $nama, $nama, $nama,
                $namaKelas, $namaKelas, $namaKelas, $namaKelas, $namaKelas, $namaKelas,
                $statusPelaporLabel, $statusPelaporLabel, $statusPelaporLabel, $statusPelaporLabel,
                $kategori, $kategori,
                $deskripsi, $deskripsi, $deskripsi, $deskripsi
            ];
            $message = str_replace($search, $replace, $template);
        }

        return $this->sendMessage($this->groupId, $message);
    }

    /**
     * Base method for sending message via WA Gateway.
     */
    protected function sendMessage(string $number, string $message): bool
    {
        if (empty($this->apiKey) || empty($this->endpoint)) {
            Log::warning('WhatsAppPengaduanService: API key atau endpoint tidak dikonfigurasi.');
            return false;
        }

        try {
            $url = $this->endpoint;
            if (!str_ends_with($url, '/send-message')) {
                $url = rtrim($url, '/') . '/send-message';
            }
            $response = Http::timeout(15)->post($url, [
                'api_key' => $this->apiKey,
                'sender'  => $this->sender,
                'number'  => $this->formatNumber($number),
                'message' => $message,
                'footer'  => 'Sistem Pengaduan Data - MAN 1 Kota Bandung',
            ]);

            $result = $response->json();

            if (isset($result['status']) && $result['status'] === true) {
                return true;
            }

            Log::warning('WhatsAppPengaduanService Failed: ' . json_encode($result));
            return false;

        } catch (\Exception $e) {
            Log::error('WhatsAppPengaduanService Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format number to international format (starting with 62)
     */
    protected function formatNumber(string $number): string
    {
        // Jika berupa group ID (mengandung @), kembalikan langsung
        if (str_contains($number, '@')) {
            return $number;
        }

        // Hanya sisakan angka saja
        $number = preg_replace('/\D/', '', $number);

        // Jika diawali '0', ganti menjadi '62'
        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        return $number;
    }
}

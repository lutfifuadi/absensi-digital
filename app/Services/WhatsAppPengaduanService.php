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
        $this->apiKey   = Pengaturan::where('key', 'wa_pengaduan_api_key')->value('value') ?? '';
        $this->endpoint = Pengaturan::where('key', 'wa_pengaduan_endpoint')->value('value') ?? 'https://wa.lutfifuadi.my.id';
        $this->sender   = Pengaturan::where('key', 'wa_pengaduan_sender')->value('value') ?? '';
        $this->groupId  = Pengaturan::where('key', 'wa_pengaduan_group_id')->value('value') ?? '';
    }

    /**
     * Kirim kode unik ke nomor pelapor.
     */
    public function sendKodeUnik(string $nomorWa, string $kodeUnik, string $nama): bool
    {
        $template = NotificationTemplate::where('type', 'pengaduan_kode_unik')->value('content');
        if (empty($template)) {
            $message = "Halo *{$nama}*,\n\n"
                . "Terima kasih telah melaporkan data tidak valid.\n\n"
                . "Berikut kode unik pengaduan Anda:\n"
                . "*{$kodeUnik}*\n\n"
                . "Simpan kode ini untuk mengecek status pengaduan Anda.\n\n"
                . "Sistem Pengaduan Data - MAN 1 Kota Bandung";
        } else {
            $message = str_replace(
                ['{nama}', '{kode_unik}'],
                [$nama, $kodeUnik],
                $template
            );
        }

        return $this->sendMessage($nomorWa, $message);
    }

    /**
     * Kirim update status ke pelapor.
     */
    public function sendStatusUpdate(string $nomorWa, string $kodeUnik, string $status, string $catatan = ''): bool
    {
        $statusLabel = match ($status) {
            'diproses' => 'Diproses',
            'selesai'  => 'Selesai',
            'ditolak'  => 'Ditolak',
            default    => ucfirst($status),
        };

        $template = NotificationTemplate::where('type', 'pengaduan_status_update')->value('content');
        if (empty($template)) {
            $message = "Halo,\n\n"
                . "Pengaduan dengan kode *{$kodeUnik}* telah diupdate.\n\n"
                . "Status: *{$statusLabel}*\n";

            if ($catatan) {
                $message .= "Catatan: {$catatan}\n";
            }

            $message .= "\nTerima kasih telah menggunakan layanan pengaduan kami.\n\n"
                . "Sistem Pengaduan Data - MAN 1 Kota Bandung";
        } else {
            $catatanText = $catatan ? "Catatan: {$catatan}\n" : '';
            $message = str_replace(
                ['{kode_unik}', '{status}', '{catatan}'],
                [$kodeUnik, $statusLabel, $catatanText],
                $template
            );
        }

        return $this->sendMessage($nomorWa, $message);
    }

    /**
     * Kirim notifikasi ke grup admin.
     */
    public function sendToGroupAdmin(string $kodeUnik, string $nama, string $statusPelapor, string $kategori, string $deskripsi): bool
    {
        $statusPelaporLabel = $statusPelapor === 'siswa' ? 'Siswa' : 'Orang Tua';

        $template = NotificationTemplate::where('type', 'pengaduan_group_admin')->value('content');
        if (empty($template)) {
            $message = "━━━ *PENGADUAN BARU* ━━━\n\n"
                . "Kode: *{$kodeUnik}*\n"
                . "Nama: {$nama}\n"
                . "Status: {$statusPelaporLabel}\n"
                . "Kategori: {$kategori}\n\n"
                . "Deskripsi:\n{$deskripsi}\n\n"
                . "Silakan proses pengaduan ini di panel admin.\n"
                . "Sistem Pengaduan Data - MAN 1 Kota Bandung";
        } else {
            $message = str_replace(
                ['{kode_unik}', '{nama}', '{status}', '{kategori}', '{deskripsi}'],
                [$kodeUnik, $nama, $statusPelaporLabel, $kategori, $deskripsi],
                $template
            );
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

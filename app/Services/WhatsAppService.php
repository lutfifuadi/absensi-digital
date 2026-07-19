<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Pengaturan;
use App\Models\Siswa;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WhatsAppService
{
    protected $baseUrl;
    protected $apiKey;
    protected $sender;
    protected $isEnabled;
    protected $notifSender; // Nomor khusus pengirim notifikasi

    public function __construct()
    {
        // on/off toggle khusus WA Gateway (terpisah dari jenis_notifikasi_ortu)
        $waEnabled = Pengaturan::where('key', 'wa_gateway_enabled')->value('value');
        $this->isEnabled = ($waEnabled === null ? true : $waEnabled === 'Ya')
            && Pengaturan::where('key', 'jenis_notifikasi_ortu')->value('value') === 'WhatsApp (WA)';

        $link = Pengaturan::where('key', 'link_server_wa')->value('value') ?: 'https://wa.lutfifuadi.my.id/send-message';
        $this->baseUrl = rtrim(str_replace(['/send-message', '/send-media', '/check-number', '/send-location'], '', $link), '/');

        // API key dan sender nomor
        $this->apiKey   = Pengaturan::where('key', 'wa_api_key')->value('value') ?: env('WA_API_KEY', '');
        $this->sender   = Pengaturan::where('key', 'wa_nomor_notifikasi')->value('value')
            ?: Pengaturan::where('key', 'nomor_server_wa_api_key')->value('value')
            ?: '';

        // Support format lama: sender|apikey dalam satu field
        if (empty($this->apiKey) || $this->apiKey === '1234567890') {
            $waConfig = Pengaturan::where('key', 'nomor_server_wa_api_key')->value('value') ?: '';
            if (strpos($waConfig, '|') !== false) {
                [$this->sender, $this->apiKey] = explode('|', $waConfig, 2);
            }
        }
    }

    /**
     * Send a text message via WhatsApp Gateway
     */
    public function sendMessage(string $number, string $message, string $footer = '', ?string $customSender = null, ?string $customApiKey = null): bool
    {
        if (!$this->isEnabled) return false;

        $apiKey = $customApiKey ?: $this->apiKey;
        $sender = $customSender ?: $this->sender;

        try {
            $response = Http::timeout(15)->post("{$this->baseUrl}/send-message", [
                'api_key' => $apiKey,
                'sender'  => $sender,
                'number'  => $number,
                'message' => $message,
                'footer'  => $footer ?: 'Sistem Absensi Otomatis'
            ]);

            $result = $response->json();
            
            if (isset($result['status']) && $result['status'] === true) {
                return true;
            }

            Log::warning('WhatsApp Gateway Failed: ' . json_encode($result));
            return false;

        } catch (\Exception $e) {
            Log::error('WhatsApp Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send message only if number is validated on WhatsApp
     * Uses cache to avoid hammering the check-number API
     */
    public function sendMessageIfValid(string $number, string $message, string $footer = '', int $siswaId = 0, ?string $customSender = null, ?string $customApiKey = null): bool
    {
        if (!$this->isEnabled) return false;

        if (!$this->isNumberValidCached($number, $siswaId, $customSender, $customApiKey)) {
            Log::info("WA: Nomor {$number} tidak terdaftar di WhatsApp, pesan tidak dikirim.");
            return false;
        }

        return $this->sendMessage($number, $message, $footer, $customSender, $customApiKey);
    }

    /**
     * Check if a WA number is valid with caching (24 jam TTL)
     */
    public function isNumberValidCached(string $number, int $siswaId = 0, ?string $customSender = null, ?string $customApiKey = null): bool
    {
        $cacheKey = 'wa_valid_' . preg_replace('/\D/', '', $number);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($number, $customSender, $customApiKey) {
            return $this->checkNumber($number, false, $customSender, $customApiKey);
        });
    }

    /**
     * Force re-check a number (clear cache then check)
     */
    public function revalidateNumber(string $number): bool
    {
        $cacheKey = 'wa_valid_' . preg_replace('/\D/', '', $number);
        Cache::forget($cacheKey);
        return $this->isNumberValidCached($number);
    }

    /**
     * Send media via WhatsApp Gateway
     */
    public function sendMedia(string $number, string $mediaType, string $url, string $caption = '', string $footer = ''): bool
    {
        if (!$this->isEnabled) return false;

        try {
            $response = Http::timeout(15)->post("{$this->baseUrl}/send-media", [
                'api_key'    => $this->apiKey,
                'sender'     => $this->sender,
                'number'     => $number,
                'media_type' => $mediaType,
                'caption'    => $caption,
                'footer'     => $footer ?: 'Sistem Absensi Otomatis',
                'url'        => $url
            ]);

            $result = $response->json();
            return isset($result['status']) && $result['status'] === true;

        } catch (\Exception $e) {
            Log::error('WhatsApp Media Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a number exists on WhatsApp
     */
    public function checkNumber(string $number, bool $force = false, ?string $customSender = null, ?string $customApiKey = null): bool
    {
        if (!$force && !$this->isEnabled) return false;

        $apiKey = $customApiKey ?: $this->apiKey;
        $sender = $customSender ?: $this->sender;

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/check-number", [
                'api_key' => $apiKey,
                'sender'  => $sender,
                'number'  => $number
            ]);

            $result = $response->json();
            return isset($result['status']) && $result['status'] === true
                && isset($result['msg']['exists']) && $result['msg']['exists'] === true;

        } catch (\Exception $e) {
            Log::error('WhatsApp Check Number Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}

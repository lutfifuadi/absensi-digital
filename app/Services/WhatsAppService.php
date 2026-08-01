<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Helpers\WhatsAppHelper;
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
        $waEnabled = feature('wa_gateway_enabled');
        $waAutoreplyEnabled = feature('wa_autoreply_enabled');
        
        $this->isEnabled = ($waEnabled && setting('jenis_notifikasi_ortu') === 'WhatsApp (WA)')
            || $waAutoreplyEnabled;

        $link = setting('link_server_wa') ?: 'https://wa.lutfifuadi.my.id/send-message';
        $this->baseUrl = rtrim(str_replace(['/send-message', '/send-media', '/check-number', '/send-location'], '', $link), '/');

        // API key dan sender nomor
        $this->apiKey   = setting('wa_api_key') ?: env('WA_API_KEY', '');
        $this->sender   = setting('wa_nomor_notifikasi')
            ?: setting('nomor_server_wa_api_key')
            ?: '';

        // Support format lama: sender|apikey dalam satu field
        if (empty($this->apiKey) || $this->apiKey === '1234567890') {
            $waConfig = setting('nomor_server_wa_api_key') ?: '';
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

        $number = WhatsAppHelper::formatNumber($number);
        if (empty($number)) return false;

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

        $number = WhatsAppHelper::formatNumber($number);
        if (empty($number)) return false;

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
        $number = WhatsAppHelper::formatNumber($number);
        if (empty($number)) return false;

        $cacheKey = 'wa_valid_' . $number;

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($number, $customSender, $customApiKey) {
            return $this->checkNumber($number, true, $customSender, $customApiKey);
        });
    }

    /**
     * Force re-check a number (clear cache then check)
     */
    public function revalidateNumber(string $number): bool
    {
        $number = WhatsAppHelper::formatNumber($number);
        if (empty($number)) return false;

        $cacheKey = 'wa_valid_' . $number;
        Cache::forget($cacheKey);
        return $this->isNumberValidCached($number);
    }

    /**
     * Send media via WhatsApp Gateway
     */
    public function sendMedia(string $number, string $mediaType, string $url, string $caption = '', string $footer = ''): bool
    {
        if (!$this->isEnabled) return false;

        $number = WhatsAppHelper::formatNumber($number);
        if (empty($number)) return false;

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
     *
     * Resilient terhadap timeout: retry manual dengan backoff (2x),
     * timeout configurable via setting `wa_http_timeout` (default 15 dtk).
     * Metode utama POST; GET hanya fallback jika server menolak POST
     * (HTTP 404/405), BUKAN untuk timeout/error lain.
     * Selalu return false (bukan throw) saat gagal.
     */
    public function checkNumber(string $number, bool $force = true, ?string $customSender = null, ?string $customApiKey = null): bool
    {
        // ── 1. Jika WA Validator Aktif & Dikonfigurasi, Gunakan WhatsAppValidatorService ──
        $valEnabled = settingBool('wa_validator_enabled') || setting('wa_validator_enabled') === 'Ya' || feature_is_on('wa_validator_enabled');
        if ($valEnabled && empty($customApiKey) && empty($customSender)) {
            /** @var WhatsAppValidatorService $validator */
            $validator = app(WhatsAppValidatorService::class);
            return $validator->validateNomor($number);
        }

        // Pengecekan keberadaan nomor di WhatsApp adalah query read-only, selalu izinkan jika force=true atau endpoint tersedia
        $number = WhatsAppHelper::formatNumber($number);
        if (empty($number)) return false;

        $apiKey = $customApiKey ?: $this->apiKey;
        $sender = $customSender ?: $this->sender;

        if (empty($apiKey) || empty($this->baseUrl)) {
            return false;
        }

        $timeout = $this->getHttpTimeout();
        $url = "{$this->baseUrl}/check-number";
        $payload = [
            'api_key' => $apiKey,
            'sender'  => $sender,
            'number'  => $number,
        ];

        // Retry manual: 3 percobaan, backoff 200ms lalu 400ms
        $attempts  = 3;
        $backoffs  = [200, 400]; // ms
        $method    = 'post';
        $lastError = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $start = microtime(true);
            $result = $this->requestCheckNumber($url, $payload, $method, $timeout);

            if ($result['error'] !== null) {
                $lastError = $result['error'];
                Log::warning(sprintf(
                    'WhatsApp Check Number: Percobaan %d gagal untuk nomor %s (%d ms): %s',
                    $attempt,
                    $this->maskNumber($number),
                    (int) round((microtime(true) - $start) * 1000),
                    $result['error']
                ));

                if ($attempt < $attempts) {
                    usleep($backoffs[$attempt - 1] * 1000);
                }
                continue;
            }

            /** @var \Illuminate\Http\Client\Response $response */
            $response = $result['response'];
            $elapsedMs = (int) round((microtime(true) - $start) * 1000);
            $status = $response->status();

            // Server menolak POST (405/404) → fallback GET sekali, tanpa memakai slot retry
            if ($method === 'post' && in_array($status, [404, 405], true)) {
                Log::info(sprintf(
                    'WhatsApp Check Number: POST tidak didukung (HTTP %d) untuk nomor %s, fallback ke GET.',
                    $status,
                    $this->maskNumber($number)
                ));
                $method = 'get';
                $attempt--; // ulangi iterasi yang sama dengan metode GET
                continue;
            }

            if ($response->successful()) {
                $resultJson = $response->json();
                $valid = isset($resultJson['status']) && $resultJson['status'] === true
                    && isset($resultJson['msg']['exists']) && $resultJson['msg']['exists'] === true;

                Log::debug(sprintf(
                    'WhatsApp Check Number: %s untuk nomor %s (HTTP %d, %d ms, percobaan %d).',
                    $valid ? 'VALID' : 'TIDAK VALID',
                    $this->maskNumber($number),
                    $status,
                    $elapsedMs,
                    $attempt
                ));
                return $valid;
            }

            Log::warning(sprintf(
                'WhatsApp Check Number: Respons HTTP %d untuk nomor %s (%d ms, percobaan %d).',
                $status,
                $this->maskNumber($number),
                $elapsedMs,
                $attempt
            ));

            // 4xx lain (selain 429) tidak akan berhasil walau diulang → berhenti
            if ($response->clientError() && $status !== 429) {
                break;
            }

            // 5xx / 429 / timeout → retry dengan backoff
            if ($attempt < $attempts) {
                usleep($backoffs[$attempt - 1] * 1000);
            }
        }

        Log::warning('WhatsApp Check Number: Gagal memvalidasi nomor ' . $this->maskNumber($number)
            . ' setelah ' . $attempts . ' percobaan (error terakhir: ' . ($lastError ?? 'HTTP non-2xx') . ').');
        return false;
    }

    /**
     * Kirim SATU request check-number dengan try/catch.
     *
     * @return array{response: ?\Illuminate\Http\Client\Response, error: ?string}
     */
    private function requestCheckNumber(string $url, array $payload, string $method, int $timeout): array
    {
        try {
            $response = $method === 'get'
                ? Http::timeout($timeout)->get($url, $payload)
                : Http::timeout($timeout)->post($url, $payload);

            return ['response' => $response, 'error' => null];
        } catch (\Exception $e) {
            // Timeout (cURL 28) / koneksi gagal → kembalikan pesan error, bukan throw
            return ['response' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Timeout HTTP request WA gateway (detik), configurable via setting `wa_http_timeout`.
     */
    private function getHttpTimeout(): int
    {
        $timeout = (int) setting('wa_http_timeout', 15);
        if ($timeout < 5 || $timeout > 60) {
            $timeout = 15;
        }
        return $timeout;
    }

    /**
     * Samarkan nomor untuk log (hindari data sensitif penuh).
     * Contoh: 6281234567890 → 62812*****890
     */
    private function maskNumber(string $number): string
    {
        $len = strlen($number);
        if ($len <= 6) {
            return substr($number, 0, 3) . str_repeat('*', max($len - 3, 1));
        }
        return substr($number, 0, 5) . str_repeat('*', $len - 7) . substr($number, -2);
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}

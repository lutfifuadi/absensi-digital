<?php

namespace App\Services;

use App\Models\Pengaturan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppValidatorService
{
    protected string $apiKey;
    protected string $endpoint;
    protected string $sender;

    public function __construct()
    {
        $this->apiKey   = setting('wa_validator_api_key', '');
        $this->endpoint = setting('wa_validator_endpoint', 'https://wa.lutfifuadi.my.id');
        $this->sender   = setting('wa_validator_sender', '');
    }

    /**
     * Validasi apakah nomor WhatsApp terdaftar dan aktif.
     * Resilient terhadap timeout: retry manual dengan backoff (2x),
     * timeout configurable via setting `wa_http_timeout` (default 15 dtk).
     * Jika API timeout/gagal, return false (bukan throw).
     */
    public function validateNomor(string $nomorWa): bool
    {
        if (empty($this->apiKey) || empty($this->endpoint)) {
            Log::warning('WhatsAppValidatorService: API key atau endpoint tidak dikonfigurasi.');
            return false;
        }

        $number = $this->formatNumber($nomorWa);
        if (empty($number)) {
            Log::warning('WhatsAppValidatorService: Nomor kosong setelah format.');
            return false;
        }

        $url = $this->endpoint;
        if (!str_ends_with($url, '/check-number')) {
            $url = rtrim($url, '/') . '/check-number';
        }

        $timeout = (int) setting('wa_http_timeout', 15);
        if ($timeout < 5 || $timeout > 60) {
            $timeout = 15;
        }

        // Retry manual: 3 percobaan, backoff 200ms lalu 400ms
        $attempts = 3;
        $backoffs = [200, 400]; // ms
        $lastError = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $start = microtime(true);

            try {
                $response = Http::timeout($timeout)->post($url, [
                    'api_key' => $this->apiKey,
                    'sender'  => $this->sender,
                    'number'  => $number,
                ]);
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning(sprintf(
                    'WhatsAppValidatorService: Percobaan %d gagal untuk nomor %s (%d ms): %s',
                    $attempt,
                    $this->maskNumber($number),
                    (int) round((microtime(true) - $start) * 1000),
                    $e->getMessage()
                ));

                if ($attempt < $attempts) {
                    usleep($backoffs[$attempt - 1] * 1000);
                }
                continue;
            }

            $elapsedMs = (int) round((microtime(true) - $start) * 1000);
            $status = $response->status();

            if ($response->successful()) {
                $result = $response->json();
                $valid = isset($result['status']) && $result['status'] === true
                    && isset($result['msg']['exists']) && $result['msg']['exists'] === true;

                Log::debug(sprintf(
                    'WhatsAppValidatorService: %s untuk nomor %s (HTTP %d, %d ms, percobaan %d).',
                    $valid ? 'VALID' : 'TIDAK VALID',
                    $this->maskNumber($number),
                    $status,
                    $elapsedMs,
                    $attempt
                ));
                return $valid;
            }

            Log::warning(sprintf(
                'WhatsAppValidatorService: Respons HTTP %d untuk nomor %s (%d ms, percobaan %d).',
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

        Log::warning('WhatsAppValidatorService: Gagal memvalidasi nomor ' . $this->maskNumber($number)
            . ' setelah ' . $attempts . ' percobaan (error terakhir: ' . ($lastError ?? 'HTTP non-2xx') . ').');
        return false;
    }

    /**
     * Samarkan nomor untuk log (hindari data sensitif penuh).
     * Contoh: 6281234567890 → 62812*****890
     */
    protected function maskNumber(string $number): string
    {
        $len = strlen($number);
        if ($len <= 6) {
            return substr($number, 0, 3) . str_repeat('*', max($len - 3, 1));
        }
        return substr($number, 0, 5) . str_repeat('*', $len - 7) . substr($number, -2);
    }

    /**
     * Format number to international format (starting with 62)
     */
    protected function formatNumber(string $number): string
    {
        // Hanya sisakan angka saja
        $number = preg_replace('/\D/', '', $number);

        // Jika diawali '0', ganti menjadi '62'
        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        return $number;
    }
}

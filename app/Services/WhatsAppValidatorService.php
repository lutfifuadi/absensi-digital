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
        $this->apiKey   = Pengaturan::where('key', 'wa_validator_api_key')->value('value') ?? '';
        $this->endpoint = Pengaturan::where('key', 'wa_validator_endpoint')->value('value') ?? 'https://wa.lutfifuadi.my.id';
        $this->sender   = Pengaturan::where('key', 'wa_validator_sender')->value('value') ?? '';
    }

    /**
     * Validasi apakah nomor WhatsApp terdaftar dan aktif.
     * Jika API timeout/gagal, return false.
     */
    public function validateNomor(string $nomorWa): bool
    {
        if (empty($this->apiKey) || empty($this->endpoint)) {
            Log::warning('WhatsAppValidatorService: API key atau endpoint tidak dikonfigurasi.');
            return false;
        }

        try {
            $url = $this->endpoint;
            if (!str_ends_with($url, '/check-number')) {
                $url = rtrim($url, '/') . '/check-number';
            }
            $response = Http::timeout(10)->post($url, [
                'api_key' => $this->apiKey,
                'sender'  => $this->sender,
                'number'  => $this->formatNumber($nomorWa),
            ]);

            $result = $response->json();

            return isset($result['status']) && $result['status'] === true
                && isset($result['msg']['exists']) && $result['msg']['exists'] === true;

        } catch (\Exception $e) {
            Log::error('WhatsAppValidatorService Exception: ' . $e->getMessage());
            return false;
        }
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

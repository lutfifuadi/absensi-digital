<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WaGatewayController extends Controller
{
    private array $waKeys = [
        'wa_gateway_enabled',
        'wa_og_preview_enabled',
        'link_server_wa',
        'wa_api_key',
        'wa_nomor_admin',
        'wa_nomor_notifikasi',
        'nomor_server_wa_api_key',
        'jeda_waktu_kirim_pesan_detik',
        'jeda_waktu_kirim_notifikasi_detik',
        'wa_http_timeout',
        'jenis_notifikasi_ortu',
        'pengiriman_notifikasi_scan_qr',
        // WA Pengaduan
        'wa_pengaduan_enabled',
        'wa_pengaduan_api_key',
        'wa_pengaduan_endpoint',
        'wa_pengaduan_sender',
        'wa_pengaduan_group_id',
        // WA Validator
        'wa_validator_enabled',
        'wa_validator_api_key',
        'wa_validator_endpoint',
        'wa_validator_sender',
        // WA Autoreply
        'wa_autoreply_enabled',
        'wa_autoreply_sender',
        'wa_autoreply_api_key',
        'wa_autoreply_endpoint',
        'wa_autoreply_webhook_token',
    ];

    public function index()
    {
        $settings = [];
        foreach ($this->waKeys as $key) {
            $settings[$key] = Pengaturan::where('key', $key)->value('value') ?? '';
        }

        // Defaults untuk field baru
        if (empty($settings['wa_gateway_enabled'])) $settings['wa_gateway_enabled'] = 'Ya';
        if (empty($settings['wa_og_preview_enabled'])) $settings['wa_og_preview_enabled'] = 'Ya';
        if (empty($settings['link_server_wa'])) $settings['link_server_wa'] = 'https://wa.lutfifuadi.my.id/send-message';

        // Defaults WA Pengaduan
        if (empty($settings['wa_pengaduan_enabled'])) $settings['wa_pengaduan_enabled'] = 'Tidak';
        if (empty($settings['wa_pengaduan_endpoint'])) $settings['wa_pengaduan_endpoint'] = 'https://wa.lutfifuadi.my.id';

        // Defaults WA Validator
        if (empty($settings['wa_validator_enabled'])) $settings['wa_validator_enabled'] = 'Tidak';
        if (empty($settings['wa_validator_endpoint'])) $settings['wa_validator_endpoint'] = 'https://wa.lutfifuadi.my.id/check-number';

        // Defaults WA Autoreply
        if (empty($settings['wa_autoreply_enabled'])) $settings['wa_autoreply_enabled'] = 'Tidak';
        if (empty($settings['wa_autoreply_endpoint'])) $settings['wa_autoreply_endpoint'] = 'https://wa.lutfifuadi.my.id';
        if (empty($settings['wa_autoreply_webhook_token'])) {
            $token = \Illuminate\Support\Str::random(32);
            Pengaturan::updateOrCreate(
                ['key' => 'wa_autoreply_webhook_token'],
                ['value' => $token, 'group' => 'wa_gateway']
            );
            $settings['wa_autoreply_webhook_token'] = $token;
        }

        return view('admin.wa-gateway.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'wa_og_preview_enabled'             => 'nullable|in:Ya,Tidak',
            'link_server_wa'                    => 'nullable|url|max:255',
            'wa_api_key'                        => 'nullable|string|max:255',
            'wa_nomor_admin'                    => 'nullable|string|max:20|regex:/^[0-9+]+$/',
            'wa_nomor_notifikasi'               => 'nullable|string|max:20|regex:/^[0-9+]+$/',
            'jeda_waktu_kirim_pesan_detik'      => 'nullable|integer|min:1|max:300',
            'jeda_waktu_kirim_notifikasi_detik' => 'nullable|integer|min:1|max:60',
            'wa_http_timeout'                   => 'nullable|integer|min:5|max:60',
            // WA Pengaduan
            'wa_pengaduan_enabled'              => 'nullable|in:Ya,Tidak',
            'wa_pengaduan_api_key'              => 'nullable|string|max:255',
            'wa_pengaduan_endpoint'             => 'nullable|url|max:255',
            'wa_pengaduan_sender'               => 'nullable|string|max:20|regex:/^[0-9+]+$/',
            'wa_pengaduan_group_id'             => 'nullable|string|max:255',
            // WA Validator
            'wa_validator_enabled'              => 'nullable|in:Ya,Tidak',
            'wa_validator_api_key'              => 'nullable|string|max:255',
            'wa_validator_endpoint'             => 'nullable|url|max:255',
            'wa_validator_sender'               => 'nullable|string|max:20|regex:/^[0-9+]+$/',
            // WA Autoreply
            'wa_autoreply_enabled'              => 'nullable|in:Ya,Tidak',
            'wa_autoreply_sender'               => 'nullable|string|max:20|regex:/^[0-9+]+$/',
            'wa_autoreply_api_key'              => 'nullable|string|max:255',
            'wa_autoreply_endpoint'             => 'nullable|url|max:255',
            'wa_autoreply_webhook_token'        => 'nullable|string|max:255',
        ], [
            'link_server_wa.url'            => 'Link server WA harus berupa URL yang valid.',
            'wa_nomor_admin.regex'          => 'Nomor admin hanya boleh berisi angka dan +.',
            'wa_nomor_notifikasi.regex'     => 'Nomor notifikasi hanya boleh berisi angka dan +.',
            'wa_pengaduan_endpoint.url'     => 'Endpoint WA Pengaduan harus berupa URL yang valid.',
            'wa_pengaduan_sender.regex'     => 'Nomor pengirim WA Pengaduan hanya boleh berisi angka dan +.',
            'wa_validator_endpoint.url'     => 'Endpoint WA Validator harus berupa URL yang valid.',
            'wa_validator_sender.regex'     => 'Nomor pengirim WA Validator hanya boleh berisi angka dan +.',
            'wa_autoreply_endpoint.url'     => 'Endpoint WA Autoreply harus berupa URL yang valid.',
            'wa_autoreply_sender.regex'     => 'Nomor pengirim WA Autoreply hanya boleh berisi angka dan +.',
        ]);

        $data = $request->only($this->waKeys);

        foreach ($data as $key => $value) {
            Pengaturan::updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '', 'group' => 'wa_gateway']
            );
        }

        return back()->with('success', 'Pengaturan WA Gateway berhasil disimpan.');
    }

    /**
     * Test koneksi ke WA Gateway
     */
    public function testConnection(Request $request)
    {
        try {
            $waService = new WhatsAppService();
            $nomor = $request->input('test_number', '');

            if (empty($nomor)) {
                return response()->json(['status' => false, 'message' => 'Nomor test wajib diisi.']);
            }

            // Sanitize nomor
            $nomor = preg_replace('/[^0-9]/', '', $nomor);
            if (!str_starts_with($nomor, '62')) {
                $nomor = '62' . ltrim($nomor, '0');
            }

            $valid = $waService->checkNumber($nomor, true);

            return response()->json([
                'status'  => $valid,
                'message' => $valid
                    ? "Nomor {$nomor} terdaftar di WhatsApp."
                    : "Nomor {$nomor} TIDAK terdaftar di WhatsApp.",
            ]);
        } catch (\Exception $e) {
            Log::error('WA Gateway Test Connection: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal terhubung ke server WA Gateway. Periksa konfigurasi URL dan API Key.']);
        }
    }

    /**
     * Cek konektivitas 3 service WA (Gateway Notif, Validator WA, Notif Pengaduan)
     */
    public function checkServicesStatus()
    {
        try {
            // 1. WA Gateway Notif
            $waNotifEnabled  = feature('wa_gateway_enabled');
            $waNotifLink     = Pengaturan::where('key', 'link_server_wa')->value('value') ?: 'https://wa.lutfifuadi.my.id/send-message';
            $waNotifApiKey   = Pengaturan::where('key', 'wa_api_key')->value('value') ?: env('WA_API_KEY', '');
            $waNotifSender   = Pengaturan::where('key', 'wa_nomor_notifikasi')->value('value') ?: '';

            // 2. Validator WA
            $waValEnabled    = feature('wa_validator_enabled');
            $waValEndpoint   = Pengaturan::where('key', 'wa_validator_endpoint')->value('value') ?: 'https://wa.lutfifuadi.my.id/check-number';
            $waValApiKey     = Pengaturan::where('key', 'wa_validator_api_key')->value('value') ?: '';
            $waValSender     = Pengaturan::where('key', 'wa_validator_sender')->value('value') ?: '';

            // 3. Notif Pengaduan
            $waPengEnabled   = feature('wa_pengaduan_enabled');
            $waPengEndpoint  = Pengaturan::where('key', 'wa_pengaduan_endpoint')->value('value') ?: 'https://wa.lutfifuadi.my.id';
            $waPengApiKey    = Pengaturan::where('key', 'wa_pengaduan_api_key')->value('value') ?: '';
            $waPengSender    = Pengaturan::where('key', 'wa_pengaduan_sender')->value('value') ?: '';

            // 4. WA Autoreply
            $waAutoEnabled   = feature('wa_autoreply_enabled');
            $waAutoEndpoint  = Pengaturan::where('key', 'wa_autoreply_endpoint')->value('value') ?: 'https://wa.lutfifuadi.my.id';
            $waAutoApiKey    = Pengaturan::where('key', 'wa_autoreply_api_key')->value('value') ?: '';
            $waAutoSender    = Pengaturan::where('key', 'wa_autoreply_sender')->value('value') ?: '';

            $pingNotif = $this->pingWaEndpoint($waNotifLink, $waNotifApiKey, $waNotifSender);
            $pingVal   = $this->pingWaEndpoint($waValEndpoint, $waValApiKey, $waValSender);
            $pingPeng  = $this->pingWaEndpoint($waPengEndpoint, $waPengApiKey, $waPengSender);
            $pingAuto  = $this->pingWaEndpoint($waAutoEndpoint, $waAutoApiKey, $waAutoSender);

            $services = [
                'wa_gateway_notif' => [
                    'name'     => 'WA Gateway Notif',
                    'enabled'  => $waNotifEnabled,
                    'endpoint' => $waNotifLink,
                    'status'   => $pingNotif['status'],
                    'message'  => $pingNotif['status'] === 'connected'
                        ? ($waNotifEnabled ? 'Server Terhubung & Fitur Aktif' : 'Server Terhubung (Fitur Nonaktif)')
                        : $pingNotif['message'],
                ],
                'validator_wa' => [
                    'name'     => 'Validator WA',
                    'enabled'  => $waValEnabled,
                    'endpoint' => $waValEndpoint,
                    'status'   => $pingVal['status'],
                    'message'  => $pingVal['status'] === 'connected'
                        ? ($waValEnabled ? 'Server Terhubung & Fitur Aktif' : 'Server Terhubung (Fitur Nonaktif)')
                        : $pingVal['message'],
                ],
                'notif_pengaduan' => [
                    'name'     => 'Notif Pengaduan WA',
                    'enabled'  => $waPengEnabled,
                    'endpoint' => $waPengEndpoint,
                    'status'   => $pingPeng['status'],
                    'message'  => $pingPeng['status'] === 'connected'
                        ? ($waPengEnabled ? 'Server Terhubung & Fitur Aktif' : 'Server Terhubung (Fitur Nonaktif)')
                        : $pingPeng['message'],
                ],
                'autoreply_wa' => [
                    'name'     => 'Autoreply WA',
                    'enabled'  => $waAutoEnabled,
                    'endpoint' => $waAutoEndpoint,
                    'status'   => $pingAuto['status'],
                    'message'  => $pingAuto['status'] === 'connected'
                        ? ($waAutoEnabled ? 'Server Terhubung & Fitur Aktif' : 'Server Terhubung (Fitur Nonaktif)')
                        : $pingAuto['message'],
                ],
            ];

            return response()->json([
                'status'   => true,
                'services' => $services,
            ]);
        } catch (\Exception $e) {
            Log::error('Check Services Status Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengecek konektivitas: ' . $e->getMessage(),
            ], 500);
        }
    }

    private array $pingCache = [];

    /**
     * Helper ping endpoint WA
     */
    private function pingWaEndpoint(string $url, string $apiKey = '', string $sender = ''): array
    {
        if (empty($url)) {
            $url = Pengaturan::where('key', 'link_server_wa')->value('value') ?: 'https://wa.lutfifuadi.my.id/send-message';
        }

        $targetUrl = $url;
        if (!str_contains($targetUrl, '/check-number') && !str_contains($targetUrl, '/send-message')) {
            $targetUrl = rtrim($targetUrl, '/') . '/check-number';
        }

        $fallbackKey = Pengaturan::where('key', 'wa_api_key')->value('value') ?: env('WA_API_KEY', '');
        $fallbackSender = Pengaturan::where('key', 'wa_nomor_notifikasi')->value('value') ?: Pengaturan::where('key', 'nomor_server_wa_api_key')->value('value') ?: '';

        $keyToUse = $apiKey ?: $fallbackKey ?: 'test';
        $senderToUse = $sender ?: $fallbackSender ?: '628123456789';

        $cacheKey = md5($targetUrl . '|' . $keyToUse . '|' . $senderToUse);
        if (isset($this->pingCache[$cacheKey])) {
            return $this->pingCache[$cacheKey];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->post($targetUrl, [
                'api_key' => $keyToUse,
                'sender'  => $senderToUse,
                'number'  => $senderToUse
            ]);

            if ($response->successful() || in_array($response->status(), [200, 400, 401, 422])) {
                $result = ['status' => 'connected', 'message' => 'Terhubung (HTTP ' . $response->status() . ')'];
            } else {
                $result = ['status' => 'disconnected', 'message' => 'Response Error (HTTP ' . $response->status() . ')'];
            }
        } catch (\Exception $e) {
            $result = ['status' => 'disconnected', 'message' => 'Tidak dapat terhubung (' . $e->getMessage() . ')'];
        }

        $this->pingCache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Batch check status validitas nomor WhatsApp (untuk tabel Siswa, Guru, Ortu)
     *
     * Dilakukan per-request dengan jeda kecil antar nomor (rate-limit) agar
     * tidak membebani server WA gateway. `force=true` tetap menghormati
     * jeda yang sama.
     */
    public function batchCheckNumbers(Request $request)
    {
        $numbers = $request->input('numbers', []);
        $force   = $request->boolean('force', false);
        if (!is_array($numbers)) {
            $numbers = [$numbers];
        }

        // Batas aman per request agar request tidak berlarut-larut
        $numbers = array_slice($numbers, 0, 50);

        $waService = new WhatsAppService();
        $results = [];
        $validCount = 0;
        $invalidCount = 0;

        foreach ($numbers as $num) {
            $formatted = \App\Helpers\WhatsAppHelper::formatNumber($num);
            if (empty($formatted)) {
                $results[$num] = false;
                $invalidCount++;
                continue;
            }

            if ($force) {
                $isValid = $waService->revalidateNumber($formatted);
            } else {
                $isValid = $waService->isNumberValidCached($formatted);
            }

            // Jeda kecil antar request ke server WA gateway (anti overload)
            if (!empty($numbers) && $num !== end($numbers)) {
                usleep(100000); // 100ms
            }

            $results[$num] = $isValid;
            $isValid ? $validCount++ : $invalidCount++;
        }

        return response()->json([
            'status'       => true,
            'results'      => $results,
            'valid_count'  => $validCount,
            'invalid_count'=> $invalidCount,
        ]);
    }

    /**
     * Chunked bulk check for ALL numbers of a specific role (1000+ records)
     *
     * Default memanfaatkan cache (24 jam) agar tidak membebani server WA;
     * `force=true` hanya untuk pemakaian yang memang butuh pengecekan ulang,
     * dan tetap di-rate-limit dengan jeda 100ms antar nomor.
     */
    public function checkAllRoleNumbers(Request $request)
    {
        $role   = $request->input('role', 'siswa');
        $offset = $request->integer('offset', 0);
        $limit  = min($request->integer('limit', 20), 25); // cap per chunk
        $force  = $request->boolean('force', false);

        $waService = new WhatsAppService();
        $numbers = [];
        $total = 0;

        if ($role === 'siswa') {
            $query = \App\Models\Siswa::whereNotNull('no_hp')->orWhereNotNull('no_hp_ortu');
            $total = $query->count();
            $items = $query->skip($offset)->take($limit)->get();
            foreach ($items as $item) {
                $raw = $item->no_hp ?: $item->no_hp_ortu;
                $formatted = \App\Helpers\WhatsAppHelper::formatNumber($raw);
                if ($formatted) $numbers[] = $formatted;
            }
        } else if ($role === 'guru') {
            $query = \App\Models\Guru::whereNotNull('no_hp');
            $total = $query->count();
            $items = $query->skip($offset)->take($limit)->get();
            foreach ($items as $item) {
                $formatted = \App\Helpers\WhatsAppHelper::formatNumber($item->no_hp);
                if ($formatted) $numbers[] = $formatted;
            }
        } else if ($role === 'orang_tua') {
            $query = \App\Models\User::where('role', \App\Models\User::ROLE_ORANG_TUA)->whereNotNull('no_hp');
            $total = $query->count();
            $items = $query->skip($offset)->take($limit)->get();
            foreach ($items as $item) {
                $formatted = \App\Helpers\WhatsAppHelper::formatNumber($item->no_hp);
                if ($formatted) $numbers[] = $formatted;
            }
        }

        $results = [];
        $validCount = 0;
        $invalidCount = 0;

        foreach ($numbers as $num) {
            // Tanpa force → pakai cache 24 jam (hindari hammering server WA)
            $results[$num] = $force
                ? $waService->revalidateNumber($num)
                : $waService->isNumberValidCached($num);

            if (!empty($numbers) && $num !== end($numbers)) {
                usleep(100000); // Jeda 100ms antar request
            }

            $results[$num] ? $validCount++ : $invalidCount++;
        }

        return response()->json([
            'status'       => true,
            'role'         => $role,
            'offset'       => $offset,
            'limit'        => $limit,
            'total'        => $total,
            'processed'    => count($numbers),
            'results'      => $results,
            'valid_count'  => $validCount,
            'invalid_count'=> $invalidCount,
        ]);
    }
}

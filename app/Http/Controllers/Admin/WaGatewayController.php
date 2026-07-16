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
        'link_server_wa',
        'wa_api_key',
        'wa_nomor_admin',
        'wa_nomor_notifikasi',
        'nomor_server_wa_api_key',
        'jeda_waktu_kirim_pesan_detik',
        'jeda_waktu_kirim_notifikasi_detik',
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
    ];

    public function index()
    {
        $settings = [];
        foreach ($this->waKeys as $key) {
            $settings[$key] = Pengaturan::where('key', $key)->value('value') ?? '';
        }

        // Defaults untuk field baru
        if (empty($settings['wa_gateway_enabled'])) $settings['wa_gateway_enabled'] = 'Ya';
        if (empty($settings['link_server_wa'])) $settings['link_server_wa'] = 'https://wa.lutfifuadi.my.id/send-message';

        // Defaults WA Pengaduan
        if (empty($settings['wa_pengaduan_enabled'])) $settings['wa_pengaduan_enabled'] = 'Tidak';
        if (empty($settings['wa_pengaduan_endpoint'])) $settings['wa_pengaduan_endpoint'] = 'https://wa.lutfifuadi.my.id';

        // Defaults WA Validator
        if (empty($settings['wa_validator_enabled'])) $settings['wa_validator_enabled'] = 'Tidak';
        if (empty($settings['wa_validator_endpoint'])) $settings['wa_validator_endpoint'] = 'https://wa.lutfifuadi.my.id/check-number';

        return view('admin.wa-gateway.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'link_server_wa'                    => 'nullable|url|max:255',
            'wa_api_key'                        => 'nullable|string|max:255',
            'wa_nomor_admin'                    => 'nullable|string|max:20|regex:/^[0-9+]+$/',
            'wa_nomor_notifikasi'               => 'nullable|string|max:20|regex:/^[0-9+]+$/',
            'jeda_waktu_kirim_pesan_detik'      => 'nullable|integer|min:1|max:300',
            'jeda_waktu_kirim_notifikasi_detik' => 'nullable|integer|min:1|max:60',
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
        ], [
            'link_server_wa.url'            => 'Link server WA harus berupa URL yang valid.',
            'wa_nomor_admin.regex'          => 'Nomor admin hanya boleh berisi angka dan +.',
            'wa_nomor_notifikasi.regex'     => 'Nomor notifikasi hanya boleh berisi angka dan +.',
            'wa_pengaduan_endpoint.url'     => 'Endpoint WA Pengaduan harus berupa URL yang valid.',
            'wa_pengaduan_sender.regex'     => 'Nomor pengirim WA Pengaduan hanya boleh berisi angka dan +.',
            'wa_validator_endpoint.url'     => 'Endpoint WA Validator harus berupa URL yang valid.',
            'wa_validator_sender.regex'     => 'Nomor pengirim WA Validator hanya boleh berisi angka dan +.',
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
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleDriveSetting;
use App\Services\GoogleDriveConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Google\Client;
use Google\Service\Drive;

class GoogleAuthController extends Controller
{
    protected $client;

    public function __construct()
    {
        $config = GoogleDriveConfigService::getConfig();

        $this->client = new Client();
        $this->client->setClientId($config['client_id']);
        $this->client->setClientSecret($config['client_secret']);
        $this->client->setRedirectUri($config['redirect_uri']);
        $this->client->addScope(Drive::DRIVE);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        
        $cacert = 'D:\Project\xampp\php\extras\ssl\cacert.pem';
        if (file_exists($cacert)) {
            $guzzleClient = new \GuzzleHttp\Client(['verify' => $cacert]);
            $this->client->setHttpClient($guzzleClient);
        }
    }

    public function redirectToGoogle()
    {
        $authUrl = $this->client->createAuthUrl();
        return Redirect::away($authUrl);
    }

    public function handleGoogleCallback(Request $request)
    {
        $error = $request->query('error');

        if ($error) {
            return redirect()->route('admin.pengaturan.index', ['tab' => 'google-drive'])
                ->with('sync_error', 'Akses Google Drive ditolak. Silakan coba lagi.');
        }

        $code = $request->query('code');

        if (!$code) {
            return redirect()->route('admin.pengaturan.index', ['tab' => 'google-drive'])
                ->with('sync_error', 'Kode otorisasi tidak ditemukan.');
        }

        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                return redirect()->route('admin.pengaturan.index', ['tab' => 'google-drive'])
                    ->with('sync_error', 'Gagal mendapatkan token: ' . ($token['error_description'] ?? $token['error']));
            }

            $setting = GoogleDriveSetting::firstOrNew();
            $setting->google_access_token = json_encode($token);
            $setting->is_connected = true;

            if (isset($token['refresh_token'])) {
                $setting->google_refresh_token = $token['refresh_token'];
            }

            if (isset($token['expires_in'])) {
                $setting->google_token_expires_at = now()->addSeconds($token['expires_in']);
            }

            $setting->save();

            return redirect()->route('admin.pengaturan.index', ['tab' => 'google-drive'])
                ->with('sync_success', 'Berhasil terhubung ke Google Drive!');
        } catch (\Exception $e) {
            return redirect()->route('admin.pengaturan.index', ['tab' => 'google-drive'])
                ->with('sync_error', 'Gagal mendapatkan token: ' . $e->getMessage());
        }
    }

    public function revokeGoogleAccess()
    {
        $setting = GoogleDriveSetting::first();

        if (!$setting || !$setting->google_access_token) {
            return redirect()->route('admin.pengaturan.index', ['tab' => 'google-drive'])
                ->with('sync_error', 'Tidak ada token yang dicabut.');
        }

        try {
            $token = json_decode($setting->google_access_token, true);
            $this->client->setAccessToken($token);
            $this->client->revokeToken();
        } catch (\Exception $e) {
            // Lanjutkan hapus dari DB meski revoke gagal
        }

        $setting->update([
            'google_access_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
            'is_connected' => false,
        ]);

        return redirect()->route('admin.pengaturan.index', ['tab' => 'google-drive'])
            ->with('sync_success', 'Akses Google Drive berhasil dicabut.');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'google_client_id' => 'nullable|string|max:255',
            'google_client_secret' => 'nullable|string|max:255',
            'google_redirect_uri' => 'nullable|url|max:255',
            'google_root_folder_id' => 'nullable|string|max:100',
        ]);

        $setting = GoogleDriveSetting::firstOrNew();
        $setting->fill($validated);

        if (!$request->filled('google_client_secret') || $request->input('google_client_secret') === '********') {
            unset($setting->google_client_secret);
        }

        $setting->save();

        return redirect()->route('admin.pengaturan.index', ['tab' => 'google-drive'])
            ->with('sync_success', 'Pengaturan Google Drive berhasil disimpan.');
    }

    public function checkGoogleStatus()
    {
        try {
            $setting = GoogleDriveSetting::first();

            if (!$setting || !$setting->is_connected || !$setting->google_access_token) {
                return response()->json([
                    'connected' => false,
                    'message' => 'Belum terhubung ke Google Drive.'
                ]);
            }

            try {
                $accessToken = json_decode($setting->google_access_token, true);
            } catch (\Exception $e) {
                $accessToken = null;
            }

            if (empty($accessToken)) {
                return response()->json([
                    'connected' => false,
                    'message' => 'Token tidak valid.'
                ]);
            }

            $this->client->setAccessToken($accessToken);

            // Jika token kadaluarsa, coba refresh
            if ($this->client->isAccessTokenExpired()) {
                $refreshToken = $setting->google_refresh_token;
                if ($refreshToken) {
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                    if (isset($newToken['error'])) {
                        // Putuskan koneksi jika refresh gagal
                        $setting->update([
                            'is_connected' => false,
                            'google_access_token' => null,
                            'google_refresh_token' => null,
                            'google_token_expires_at' => null,
                        ]);
                        return response()->json([
                            'connected' => false,
                            'message' => 'Token kadaluarsa dan gagal memproses ulang: ' . ($newToken['error_description'] ?? $newToken['error'])
                        ]);
                    }

                    $accessToken = array_merge($accessToken, $newToken);
                    $this->client->setAccessToken($accessToken);

                    // Simpan token baru
                    $setting->google_access_token = json_encode($accessToken);
                    if (isset($newToken['expires_in'])) {
                        $setting->google_token_expires_at = now()->addSeconds($newToken['expires_in']);
                    }
                    $setting->save();
                } else {
                    return response()->json([
                        'connected' => false,
                        'message' => 'Token kadaluarsa dan refresh token tidak tersedia.'
                    ]);
                }
            }

            // Inisialisasi Drive Service
            $service = new Drive($this->client);
            $about = $service->about->get([
                'fields' => 'user, storageQuota'
            ]);

            $email = $about->getUser()->getEmailAddress();
            $storageQuota = $about->getStorageQuota();
            
            $limit = (float) $storageQuota->getLimit(); // bytes
            $usage = (float) $storageQuota->getUsage(); // bytes

            // 1 GB = 1073741824 bytes
            $limitGb = round($limit / 1073741824, 2);
            $usageGb = round($usage / 1073741824, 2);
            
            // Atasi limit == 0 untuk akun unlimited/shared drive
            $percent = $limit > 0 ? round(($usage / $limit) * 100, 2) : 0;

            return response()->json([
                'connected' => true,
                'email' => $email,
                'storage' => [
                    'limit_gb' => $limitGb,
                    'usage_gb' => $usageGb,
                    'used_percent' => $percent,
                    'is_unlimited' => $limit == 0,
                ],
                'root_folder_id' => $setting->google_root_folder_id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'message' => 'Kesalahan koneksi Google Drive: ' . $e->getMessage()
            ], 200); // Kembalikan HTTP 200 agar di frontend bisa dihandle dengan baik sebagai not connected
        }
    }
}

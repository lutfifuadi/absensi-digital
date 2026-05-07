<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UpdateService;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    protected UpdateService $updateService;

    public function __construct(UpdateService $updateService)
    {
        $this->updateService = $updateService;
    }

    public function index()
    {
        $currentVersion = $this->updateService->getCurrentVersion();
        $updateInfo = $this->updateService->getCachedUpdateInfo();
        
        return view('admin.update.index', compact('currentVersion', 'updateInfo'));
    }

    public function check()
    {
        $result = $this->updateService->checkForUpdates();
        
        if ($result['status']) {
            return response()->json([
                'success' => true,
                'update_available' => $result['update_available'],
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 422);
    }

    public function update()
    {
        $result = $this->updateService->runUpdate();
        
        if ($result['status']) {
            return response()->json([
                'success' => true,
                'message' => 'Sistem berhasil diperbarui ke versi terbaru.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }

    public function publishAssets()
    {
        // 1. Validasi Lisensi via API Pusat sebelum update assets
        $licenseKey = env('LICENSE_KEY');
        $domain = env('REGISTERED_DOMAIN');

        if (empty($licenseKey) || empty($domain)) {
            return response()->json([
                'success' => false,
                'message' => 'Lisensi atau Domain belum dikonfigurasi. Silakan aktifkan lisensi terlebih dahulu.',
            ], 403);
        }

        // Bypass for development
        if ($licenseKey === 'DEV-MASTER-KEY') {
            return $this->executePublishAssets();
        }

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()->timeout(20)->post('https://saas-presensi.lutfifuadi.my.id/api/license/verify', [
                'license_key' => $licenseKey,
                'domain' => $domain,
            ]);

            $result = $response->json();

            if (!$response->successful() || empty($result['success'])) {
                $errorMsg = 'Lisensi tidak valid. Proses update assets dibatalkan.';
                if (isset($result['message'])) {
                    $errorMsg .= ' Detail: ' . $result['message'];
                }
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                ], 403);
            }

            return $this->executePublishAssets();

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('License Check Error during Asset Update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal verifikasi lisensi (Server Error). Pastikan server terhubung ke internet.',
            ], 500);
        }
    }

    /**
     * Internal method to execute the actual asset publishing.
     */
    private function executePublishAssets()
    {
        try {
            try {
                \Illuminate\Support\Facades\Artisan::call('livewire:publish', ['--assets' => true, '--force' => true]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Artisan::call('vendor:publish', ['--tag' => 'livewire:assets', '--force' => true]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Livewire assets berhasil dipublish ke public/vendor/livewire/.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal publish assets: ' . $e->getMessage(),
            ], 500);
        }
    }
}

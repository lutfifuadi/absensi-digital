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
        
        // Cek apakah sistem diinstall menggunakan Git. Jika iya, bandingkan commit lokal dengan remote.
        $deployService = app(\App\Services\DeployService::class);
        $envCheck = $deployService->checkEnvironment();
        $isGitAvailable = $envCheck['git']['available'] ?? false;

        if ($isGitAvailable && \Illuminate\Support\Facades\File::exists(base_path('.git'))) {
            try {
                // Ambil daftar commit terbaru dari remote
                $fetchProcess = new \Symfony\Component\Process\Process(['git', 'fetch', 'origin'], base_path());
                $fetchProcess->run();
                
                $versionInfo = $deployService->checkVersion();
                if (isset($versionInfo['behind']) && $versionInfo['behind'] > 0) {
                    $result['status'] = true;
                    $result['update_available'] = true;
                    $result['latest_version'] = 'Update via Git';
                    $result['changelog'] = "Terdapat {$versionInfo['behind']} pembaruan (commit) baru di GitHub. Klik 'Perbarui' untuk melakukan penarikan kode dan update aset otomatis.";
                    $result['update_data'] = [
                        'latest_version' => 'Update via Git',
                        'changelog' => "Terdapat {$versionInfo['behind']} pembaruan (commit) baru di GitHub.",
                        'package_url' => '',
                        'release_date' => now()->toDateTimeString()
                    ];
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Git update check error: ' . $e->getMessage());
            }
        }
        
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

    public function update(Request $request)
    {
        // 1. Validasi Lisensi via API Pusat sebelum update
        $licenseKey = env('LICENSE_KEY');
        $domain = env('REGISTERED_DOMAIN');

        if (empty($licenseKey) || empty($domain)) {
            return response()->json([
                'success' => false,
                'message' => 'Lisensi atau Domain belum dikonfigurasi. Silakan aktifkan lisensi terlebih dahulu.',
            ], 403);
        }

        // Bypass for development
        if ($licenseKey !== 'DEV-MASTER-KEY') {
            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()->asForm()->timeout(20)->post('https://saas-presensi.lutfifuadi.my.id/api/license/verify', [
                    'license_key' => $licenseKey,
                    'domain' => $domain,
                ]);

                $result = $response->json();

                if (!$response->successful() || empty($result['success'])) {
                    $errorMsg = 'Lisensi tidak valid. Proses update dibatalkan.';
                    if (isset($result['message'])) {
                        $errorMsg .= ' Detail: ' . $result['message'];
                    }
                    return response()->json([
                        'success' => false,
                        'message' => $errorMsg,
                    ], 403);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('License Check Error during System Update: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal verifikasi lisensi (Server Error). Pastikan server terhubung ke internet.',
                ], 500);
            }
        }

        // Set time limit
        if (function_exists('set_time_limit')) {
            @set_time_limit(600);
        }
        ini_set('max_execution_time', 600);

        // 2. Deteksi metode update: Jika Git tersedia, jalankan Git Deploy secara sinkron
        $deployService = app(\App\Services\DeployService::class);
        $envCheck = $deployService->checkEnvironment();
        $isGitAvailable = $envCheck['git']['available'] ?? false;

        if ($isGitAvailable && \Illuminate\Support\Facades\File::exists(base_path('.git'))) {
            if (\Illuminate\Support\Facades\Cache::get('deploy_running')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proses update (deploy) sedang berjalan.'
                ], 409);
            }

            \Illuminate\Support\Facades\Cache::put('deploy_running', true, 3600);
            
            $deployLog = \App\Models\DeployLog::create([
                'status' => 'running',
                'triggered_by' => $request->user()->id,
                'started_at' => now(),
            ]);
            $progress = [];

            try {
                $deployService->runDeploy($request->user(), $deployLog, $progress);
                return response()->json([
                    'success' => true,
                    'message' => 'Sistem (Git Deploy) berhasil diperbarui ke versi terbaru.'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Update via Git gagal: ' . $e->getMessage()
                ], 500);
            }
        }

        // Fallback: Jalankan update via ZIP konvensional
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
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()->asForm()->timeout(20)->post('https://saas-presensi.lutfifuadi.my.id/api/license/verify', [
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

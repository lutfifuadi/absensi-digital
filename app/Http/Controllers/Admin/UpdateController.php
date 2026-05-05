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

    public function buildAssets()
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(600);
        }
        ini_set('max_execution_time', 600);

        if (!function_exists('exec')) {
            return response()->json([
                'success' => false,
                'message' => 'Fungsi exec() dinonaktifkan di server ini. Build harus dijalankan manual via SSH.'
            ], 422);
        }

        $basePath = base_path();

        // Cari path npm secara eksplisit (diperlukan di environment web server)
        $npmPath = 'npm';
        $candidates = ['/usr/bin/npm', '/usr/local/bin/npm', '/opt/homebrew/bin/npm'];
        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                $npmPath = $candidate;
                break;
            }
        }

        // Verifikasi Node.js
        $nodeCmd = str_replace('npm', 'node', $npmPath);
        exec($nodeCmd . ' -v 2>&1', $nodeCheck, $nodeExit);
        if ($nodeExit !== 0) {
            // Coba cari node secara terpisah
            exec('node -v 2>&1', $nodeCheck2, $nodeExit2);
            if ($nodeExit2 !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Node.js tidak ditemukan di server. Pastikan Node.js sudah terinstall.'
                ], 422);
            }
        }

        // Jalankan npm install
        $installCmd = "cd " . escapeshellarg($basePath) . " && " . $npmPath . " install --legacy-peer-deps 2>&1";
        exec($installCmd, $installOut, $installCode);
        if ($installCode !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'npm install gagal: ' . implode("\n", array_slice($installOut, -10))
            ], 500);
        }

        // Jalankan npm run build
        $buildCmd = "cd " . escapeshellarg($basePath) . " && " . $npmPath . " run build 2>&1";
        exec($buildCmd, $buildOut, $buildCode);
        if ($buildCode !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'npm run build gagal: ' . implode("\n", array_slice($buildOut, -10))
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Assets berhasil di-build! Halaman akan di-refresh.'
        ]);
    }
}

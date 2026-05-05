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
        $output = [];
        $exitCode = 0;

        exec('node -v 2>&1', $nodeCheck, $nodeExit);
        if ($nodeExit !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Node.js tidak ditemukan di server. Pastikan Node.js sudah terinstall.'
            ], 422);
        }

        $basePath = base_path();

        exec("cd " . escapeshellarg($basePath) . " && npm install 2>&1", $installOut, $installCode);
        if ($installCode !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'npm install gagal: ' . implode("\n", array_slice($installOut, -5))
            ], 500);
        }

        exec("cd " . escapeshellarg($basePath) . " && npm run build 2>&1", $buildOut, $buildCode);
        if ($buildCode !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'npm run build gagal: ' . implode("\n", array_slice($buildOut, -5))
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Assets berhasil di-build! Halaman akan di-refresh.'
        ]);
    }
}

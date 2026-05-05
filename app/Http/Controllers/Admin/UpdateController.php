<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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
        // Assets sudah di-build otomatis via GitHub Actions setiap push ke main.
        // Paket update dari GitHub sudah menyertakan public/build/ yang siap pakai.
        // Di sini cukup bersihkan cache Laravel agar perubahan aktif.
        try {
            Artisan::call('optimize:clear');
            Artisan::call('view:clear');

            return response()->json([
                'success' => true,
                'message' => 'Cache berhasil dibersihkan. Assets sudah tersedia (di-build via GitHub Actions).'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan cache: ' . $e->getMessage()
            ], 500);
        }
    }
}

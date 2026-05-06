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
        try {
            $output = '';
            try {
                \Illuminate\Support\Facades\Artisan::call('livewire:publish', ['--assets' => true, '--force' => true]);
                $output = \Illuminate\Support\Facades\Artisan::output();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Artisan::call('vendor:publish', ['--tag' => 'livewire:assets', '--force' => true]);
                $output = \Illuminate\Support\Facades\Artisan::output();
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

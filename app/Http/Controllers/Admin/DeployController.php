<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DeployJob;
use App\Models\DeployLog;
use App\Services\DeployService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DeployController extends Controller
{
    public function index()
    {
        $status = app(DeployService::class)->checkEnvironment();
        $queueRunning = app(DeployService::class)->checkQueueHeartbeat();
        $version = app(DeployService::class)->checkVersion();
        $history = DeployLog::latest()->take(50)->get();
        $isRunning = Cache::get('deploy_running', false);

        return view('admin.deploy.index', compact('status', 'queueRunning', 'version', 'history', 'isRunning'));
    }

    public function status()
    {
        $service = app(DeployService::class);
        $version = $service->checkVersion();

        return response()->json([
            'queue_running' => $service->checkQueueHeartbeat(),
            'current_version' => $version['local'] ?? 'N/A',
            'latest_version' => $version['remote'] ?? 'N/A',
            'can_deploy' => $service->checkQueueHeartbeat() && !Cache::get('deploy_running'),
        ]);
    }

    public function progress()
    {
        return response()->json(Cache::get('deploy_progress', [
            'percentage' => 0,
            'step' => '',
            'log' => [],
            'status' => 'idle',
        ]));
    }

    public function run(Request $request)
    {
        $request->validate(['password' => 'required']);

        if (!Hash::check($request->password, $request->user()->password)) {
            return response()->json(['success' => false, 'message' => 'Password salah.'], 403);
        }

        if (Cache::get('deploy_running')) {
            return response()->json(['success' => false, 'message' => 'Deploy sedang berjalan.'], 409);
        }

        Cache::put('deploy_running', true, 3600);
        dispatch(new DeployJob($request->user()));

        return response()->json(['success' => true, 'message' => 'Deploy dimulai.']);
    }

    public function rollback(Request $request, DeployLog $deployLog)
    {
        $request->validate(['password' => 'required']);

        if (!Hash::check($request->password, $request->user()->password)) {
            return response()->json(['success' => false, 'message' => 'Password salah.'], 403);
        }

        if (!in_array($deployLog->status, ['failed'])) {
            return response()->json(['success' => false, 'message' => 'Rollback hanya untuk deploy yang gagal.'], 422);
        }

        if (empty($deployLog->backup_path)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada backup untuk deploy ini.'], 422);
        }

        try {
            app(DeployService::class)->rollback($deployLog->id);
            return response()->json(['success' => true, 'message' => 'Rollback berhasil.']);
        } catch (\Exception $e) {
            Log::error('Rollback failed', [
                'deploy_log_id' => $deployLog->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Rollback gagal. Silakan cek log untuk detail.'], 500);
        }
    }

    public function history()
    {
        return DeployLog::latest()->take(50)->get()->map(fn($log) => [
            'id' => $log->id,
            'version' => $log->version,
            'status' => $log->status,
            'duration' => $log->duration_seconds,
            'started_at' => $log->started_at?->format('d M Y H:i'),
            'triggered_by' => $log->trigger?->name,
        ]);
    }
}

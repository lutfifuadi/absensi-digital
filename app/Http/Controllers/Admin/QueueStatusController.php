<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UploadBatch;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class QueueStatusController extends Controller
{
    /**
     * Check the status of the upload queue worker.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $heartbeat = Cache::get('queue_uploads_heartbeat');

        $status = 'inactive';
        $lastHeartbeat = null;

        if ($heartbeat) {
            $heartbeatTime = Carbon::parse($heartbeat);
            $diffInMinutes = $heartbeatTime->diffInMinutes(now());

            if ($diffInMinutes < 2) {
                $status = 'active';
            }

            $lastHeartbeat = $heartbeat;
        }

        // Hitung batch dengan status 'pending' yang sudah > 5 menit
        $stalePendingBatches = \App\Models\UploadBatch::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(5))
            ->count();

        return response()->json([
            'status' => $status,
            'last_heartbeat' => $lastHeartbeat,
            'pending_batches' => $stalePendingBatches,
        ]);
    }
}
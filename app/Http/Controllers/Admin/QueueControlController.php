<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupervisorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class QueueControlController extends Controller
{
    protected SupervisorService $supervisorService;

    public function __construct(SupervisorService $supervisorService)
    {
        $this->supervisorService = $supervisorService;
    }

    /**
     * GET /admin/queue/status
     * 
     * Mendapatkan status queue worker (running/stopped) + info detail.
     */
    public function status(): JsonResponse
    {
        try {
            $result = $this->supervisorService->getStatus();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'status' => $result['status'],
                'process_info' => $result['process_info'],
            ]);
        } catch (\Exception $e) {
            Log::error('QueueControlController::status error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan status worker: ' . $e->getMessage(),
                'status' => 'stopped',
            ], 500);
        }
    }

    /**
     * POST /admin/queue/start
     * 
     * Start queue worker via Supervisor API.
     */
    public function start(): JsonResponse
    {
        try {
            $this->supervisorService->start();

            return response()->json([
                'success' => true,
                'message' => 'Queue worker berhasil di-start.',
                'status' => 'running',
            ]);
        } catch (\Exception $e) {
            Log::error('QueueControlController::start error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal start worker: ' . $e->getMessage(),
                'status' => 'stopped',
            ], 500);
        }
    }

    /**
     * POST /admin/queue/stop
     * 
     * Stop queue worker via Supervisor API.
     */
    public function stop(): JsonResponse
    {
        try {
            $this->supervisorService->stop();

            return response()->json([
                'success' => true,
                'message' => 'Queue worker berhasil di-stop.',
                'status' => 'stopped',
            ]);
        } catch (\Exception $e) {
            Log::error('QueueControlController::stop error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal stop worker: ' . $e->getMessage(),
                'status' => 'running',
            ], 500);
        }
    }

    /**
     * POST /admin/queue/restart
     * 
     * Restart queue worker via Supervisor API.
     */
    public function restart(): JsonResponse
    {
        try {
            $this->supervisorService->restart();

            return response()->json([
                'success' => true,
                'message' => 'Queue worker berhasil di-restart.',
                'status' => 'running',
            ]);
        } catch (\Exception $e) {
            Log::error('QueueControlController::restart error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal restart worker: ' . $e->getMessage(),
                'status' => 'stopped',
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupervisorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
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

            if (request()->boolean('auto_start') && $result['status'] === 'stopped') {
                $this->ensureSupervisorEnvConfigured();
                $this->supervisorService->start();
                $result['status'] = 'running';
                $result['message'] = 'Worker otomatis di-start.';
            }

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
            $this->ensureSupervisorEnvConfigured();
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

    /**
     * Memastikan konfigurasi Supervisor di file .env sudah ada dan terisi.
     */
    private function ensureSupervisorEnvConfigured(): void
    {
        try {
            $username = env('SUPERVISOR_API_USERNAME');
            $password = env('SUPERVISOR_API_PASSWORD');

            if (empty($username) || empty($password)) {
                $envPath = base_path('.env');

                if (file_exists($envPath)) {
                    $envContent = file_get_contents($envPath);

                    $defaults = [
                        'SUPERVISOR_API_HOST' => '127.0.0.1',
                        'SUPERVISOR_API_PORT' => '9001',
                        'SUPERVISOR_API_USERNAME' => 'supervisor_api',
                        'SUPERVISOR_API_PASSWORD' => '06Juni2017!',
                        'SUPERVISOR_PROGRAM_NAME' => 'laravel-worker',
                    ];

                    $updated = false;

                    foreach ($defaults as $key => $value) {
                        if (preg_match("/^{$key}=.*/m", $envContent)) {
                            // Jika key ada tapi nilainya kosong (misal SUPERVISOR_API_USERNAME=)
                            if (preg_match("/^{$key}=\s*$/m", $envContent)) {
                                $envContent = preg_replace("/^{$key}=\s*$/m", "{$key}={$value}", $envContent);
                                $updated = true;
                            }
                        } else {
                            // Jika key belum ada di .env sama sekali
                            $envContent .= "\n{$key}={$value}";
                            $updated = true;
                        }
                    }

                    if ($updated) {
                        file_put_contents($envPath, $envContent);
                        Artisan::call('config:clear');
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('QueueControlController::ensureSupervisorEnvConfigured error: ' . $e->getMessage());
        }
    }
}

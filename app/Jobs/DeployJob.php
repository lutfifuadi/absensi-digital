<?php

namespace App\Jobs;

use App\Models\DeployLog;
use App\Models\User;
use App\Services\DeployService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DeployJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 1;

    public function __construct(public User $user) {}

    public function handle(DeployService $service): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $progress = [
            'percentage' => 0,
            'step' => 'Memulai deploy...',
            'log' => [],
            'status' => 'running',
        ];
        Cache::put('deploy_progress', $progress, 600);

        $deployLog = DeployLog::create([
            'status' => 'running',
            'triggered_by' => $this->user->id,
            'started_at' => now(),
        ]);

        try {
            $service->runDeploy($this->user, $deployLog, $progress);
        } catch (\Exception $e) {
            Log::error('DeployJob failed: ' . $e->getMessage(), [
                'deploy_log_id' => $deployLog->id,
                'user_id' => $this->user->id,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('DeployJob escaped failure: ' . $exception->getMessage());
        Cache::forget('deploy_running');
        Cache::forget('deploy_progress');
    }
}

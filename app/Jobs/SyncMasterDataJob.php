<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SyncMasterDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum time the job may run.
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        Artisan::call('sync:master-siswa');
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncMasterDataJob failed.', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

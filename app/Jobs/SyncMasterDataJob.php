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

    protected $tahunAkademikId;
    protected $kelasId;

    /**
     * Create a new job instance.
     */
    public function __construct($tahunAkademikId = null, $kelasId = null)
    {
        $this->tahunAkademikId = $tahunAkademikId;
        $this->kelasId = $kelasId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        Artisan::call('sync:master-siswa', [
            '--tahun_akademik_id' => $this->tahunAkademikId,
            '--kelas_id' => $this->kelasId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SyncMasterDataJob failed.', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

<?php

namespace App\Jobs;

use App\Services\EkskulWhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEkskulAlphaNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika gagal.
     */
    public int $tries = 3;

    /**
     * Backoff dalam detik antara percobaan: 30s, 60s, 120s.
     */
    public array $backoff = [30, 60, 120];

    /**
     * Timeout maksimum job dalam detik.
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     *
     * @param  int     $siswaId
     * @param  int     $ekskulId
     * @param  string  $tanggal   Format: Y-m-d
     */
    public function __construct(
        public int $siswaId,
        public int $ekskulId,
        public string $tanggal
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(EkskulWhatsAppService $ekskulWaService): void
    {
        $sent = $ekskulWaService->notifyAlpha(
            $this->siswaId,
            $this->ekskulId,
            $this->tanggal
        );

        if (!$sent) {
            throw new \RuntimeException(
                "Gagal mengirim notifikasi WA untuk siswa {$this->siswaId} pada ekskul {$this->ekskulId}"
            );
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendEkskulAlphaNotification Job Failed: siswa {$this->siswaId}, ekskul {$this->ekskulId} — " . $exception->getMessage());
    }
}

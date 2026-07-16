<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WhatsAppPengaduanService;
use Illuminate\Support\Facades\Log;

class SendPengaduanKodeUnikJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika gagal.
     */
    public int $tries = 3;

    /**
     * Backoff dalam detik antara percobaan: 30s, 60s, 120s
     */
    public array $backoff = [30, 60, 120];

    /**
     * Timeout maksimum job dalam detik.
     */
    public int $timeout = 60;

    public string $nomorWa;
    public string $kodeUnik;
    public string $nama;

    /**
     * Create a new job instance.
     */
    public function __construct(string $nomorWa, string $kodeUnik, string $nama)
    {
        $this->nomorWa  = $nomorWa;
        $this->kodeUnik = $kodeUnik;
        $this->nama     = $nama;
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppPengaduanService $service): void
    {
        $sent = $service->sendKodeUnik($this->nomorWa, $this->kodeUnik, $this->nama);

        if (!$sent) {
            Log::warning("SendPengaduanKodeUnikJob: Gagal mengirim WA ke {$this->nomorWa}. Attempt: {$this->attempts()}");

            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff[$this->attempts() - 1] ?? 120);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendPengaduanKodeUnikJob Failed untuk {$this->nomorWa}: " . $exception->getMessage());
    }
}

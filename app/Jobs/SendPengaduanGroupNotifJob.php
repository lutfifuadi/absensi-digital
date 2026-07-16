<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WhatsAppPengaduanService;
use Illuminate\Support\Facades\Log;

class SendPengaduanGroupNotifJob implements ShouldQueue
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

    public string $kodeUnik;
    public string $nama;
    public string $statusPelapor;
    public string $kategori;
    public string $deskripsi;

    /**
     * Create a new job instance.
     */
    public function __construct(string $kodeUnik, string $nama, string $statusPelapor, string $kategori, string $deskripsi)
    {
        $this->kodeUnik     = $kodeUnik;
        $this->nama         = $nama;
        $this->statusPelapor = $statusPelapor;
        $this->kategori     = $kategori;
        $this->deskripsi    = $deskripsi;
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppPengaduanService $service): void
    {
        $sent = $service->sendToGroupAdmin(
            $this->kodeUnik,
            $this->nama,
            $this->statusPelapor,
            $this->kategori,
            $this->deskripsi
        );

        if (!$sent) {
            Log::warning("SendPengaduanGroupNotifJob: Gagal kirim notif grup. Kode: {$this->kodeUnik}. Attempt: {$this->attempts()}");

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
        Log::error("SendPengaduanGroupNotifJob Failed untuk {$this->kodeUnik}: " . $exception->getMessage());
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
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

    public string $number;
    public string $message;
    public string $footer;
    public bool $validateNumber;
    public int $siswaId;
    public ?string $customSender;
    public ?string $customApiKey;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $number,
        string $message,
        string $footer = '',
        bool $validateNumber = true,
        int $siswaId = 0,
        ?string $customSender = null,
        ?string $customApiKey = null
    ) {
        $this->number         = $number;
        $this->message        = $message;
        $this->footer         = $footer;
        $this->validateNumber = $validateNumber;
        $this->siswaId        = $siswaId;
        $this->customSender   = $customSender;
        $this->customApiKey   = $customApiKey;
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $waService): void
    {
        if ($this->validateNumber) {
            $sent = $waService->sendMessageIfValid(
                $this->number,
                $this->message,
                $this->footer,
                $this->siswaId,
                $this->customSender,
                $this->customApiKey
            );
        } else {
            $sent = $waService->sendMessage($this->number, $this->message, $this->footer, $this->customSender, $this->customApiKey);
        }

        if (!$sent) {
            Log::warning("SendWhatsAppMessage: Gagal mengirim ke {$this->number}. Attempt: {$this->attempts()}");
            // Jika masih ada percobaan, release kembali ke queue
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff[$this->attempts() - 1] ?? 120);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendWhatsAppMessage Job Failed untuk {$this->number}: " . $exception->getMessage());
    }
}

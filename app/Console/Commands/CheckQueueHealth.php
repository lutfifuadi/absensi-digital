<?php

namespace App\Console\Commands;

use App\Models\UploadBatch;
use App\Models\UploadBatchItem;
use App\Notifications\BatchUploadCompletedNotification;
use Illuminate\Console\Command;

class CheckQueueHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:check-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membatalkan batch upload yang stuck di status pending lebih dari 10 menit (queue worker tidak merespon)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff = now()->subMinutes(10);

        $stuckBatches = UploadBatch::where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->get();

        $count = $stuckBatches->count();

        if ($count === 0) {
            $this->info('Tidak ada batch yang stuck. Semua sehat!');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$count} batch yang stuck. Proses pembatalan...");
        $this->newLine();

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($stuckBatches as $batch) {
            $this->cancelBatch($batch);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Dibatalkan {$count} batch yang stuck.");

        return self::SUCCESS;
    }

    /**
     * Cancel a stuck batch and notify the user.
     */
    protected function cancelBatch(UploadBatch $batch): void
    {
        $reason = 'Batch dibatalkan otomatis karena queue worker tidak merespon';

        // Update batch status ke failed
        $batch->update([
            'status'        => 'failed',
            'failed_reason' => $reason,
        ]);

        // Update semua item yang masih pending menjadi cancelled
        UploadBatchItem::where('upload_batch_id', $batch->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        // Kirim notifikasi ke user pembuat batch
        if ($batch->user) {
            $batch->user->notify(new BatchUploadCompletedNotification($batch));
        }

        // Log ke command output
        $this->line("  [{$batch->id}] {$batch->nama_batch} — dibatalkan");
    }
}

<?php

namespace App\Notifications;

use App\Models\UploadBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BatchUploadCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected UploadBatch $batch;

    public function __construct(UploadBatch $batch)
    {
        $this->batch = $batch;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $isAllFailed = $this->batch->failed_count === $this->batch->total_items;
        
        return [
            'type' => 'batch_upload_completed',
            'batch_id' => $this->batch->id,
            'title' => $isAllFailed ? 'Upload Gagal' : 'Upload Selesai',
            'message' => "Batch '{$this->batch->nama_batch}' selesai diproses. {$this->batch->success_count} sukses, {$this->batch->failed_count} gagal.",
            'icon' => 'upload',
            'color' => $this->batch->failed_count > 0 ? ($isAllFailed ? 'danger' : 'warning') : 'success',
            'url' => route('admin.upload-massal.batches.show', $this->batch->id),
            'metadata' => [
                'total_items' => $this->batch->total_items,
                'success_count' => $this->batch->success_count,
                'failed_count' => $this->batch->failed_count,
            ]
        ];
    }
}

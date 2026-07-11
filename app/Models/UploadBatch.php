<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Notifications\BatchUploadCompletedNotification;

class UploadBatch extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'upload_batches';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nama_batch',
        'sumber',
        'file_zip',
        'total_items',
        'success_count',
        'failed_count',
        'status',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_items' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the user who uploaded the batch.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in this batch.
     */
    public function items(): HasMany
    {
        return $this->hasMany(UploadBatchItem::class, 'upload_batch_id');
    }

    /**
     * Scope a query to only include pending batches.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include processing batches.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope a query to only include completed batches.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed batches.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check if status is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if status is processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if status is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if status is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if status is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Update progress and status based on items.
     */
    public function updateProgress(): bool
    {
        $total = $this->items()->count();
        $success = $this->items()->where('status', 'success')->count();
        $failed = $this->items()->where('status', 'failed')->count();
        $processing = $this->items()->where('status', 'processing')->count();

        $oldStatus = $this->status;
        $status = $oldStatus;

        if ($total > 0) {
            if ($success + $failed === $total) {
                $status = $failed === $total ? 'failed' : 'completed';
            } elseif ($processing > 0 || ($success + $failed > 0)) {
                $status = 'processing';
            }
        }

        $updated = $this->update([
            'total_items' => $total,
            'success_count' => $success,
            'failed_count' => $failed,
            'status' => $status,
        ]);

        // Trigger notifikasi jika status berubah menjadi completed atau failed
        if ($updated && in_array($status, ['completed', 'failed']) && $oldStatus !== $status) {
            if ($this->user) {
                $this->user->notify(new BatchUploadCompletedNotification($this));
            }
        }

        return $updated;
    }

    /**
     * Get the progress percentage.
     */
    public function progressPercent(): int
    {
        if ($this->total_items <= 0) {
            return 0;
        }

        return (int) round((($this->success_count + $this->failed_count) / $this->total_items) * 100);
    }
}

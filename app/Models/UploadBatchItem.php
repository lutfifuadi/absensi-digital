<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadBatchItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'upload_batch_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'upload_batch_id',
        'siswa_id',
        'original_filename',
        'stored_path',
        'google_drive_file_id',
        'old_file_id',
        'status',
        'error_message',
        'retry_count',
        'file_size',
        'mime_type',
        'processed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'upload_batch_id' => 'integer',
        'siswa_id' => 'integer',
        'retry_count' => 'integer',
        'file_size' => 'integer',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the batch that owns the item.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(UploadBatch::class, 'upload_batch_id');
    }

    /**
     * Get the siswa associated with the item.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Scope a query to only include pending items.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include processing items.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope a query to only include success items.
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include failed items.
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
     * Check if status is success.
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if status is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark item as processing.
     */
    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => 'processing',
        ]);
    }

    /**
     * Mark item as success.
     */
    public function markAsSuccess(?string $googleDriveFileId, ?string $oldFileId = null): bool
    {
        return $this->update([
            'status' => 'success',
            'google_drive_file_id' => $googleDriveFileId,
            'old_file_id' => $oldFileId,
            'processed_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark item as failed.
     */
    public function markAsFailed(string $errorMessage): bool
    {
        return $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'processed_at' => now(),
            'retry_count' => $this->retry_count + 1,
        ]);
    }
}

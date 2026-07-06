<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class GoogleSheetSetting extends Model
{
    use HasFactory;

    const CHUNK_SIZE = 50;

    protected $fillable = [
        'spreadsheet_id',
        'sheet_range',
        'type',
        'credentials_json',
        'column_mapping',
        'last_sync_at',
        'last_sync_status',
        'last_sync_message',
        'sync_total_rows',
        'sync_processed_rows',
        'sync_offset',
        'is_active',
    ];

    protected $casts = [
        'column_mapping' => 'array',
        'is_active' => 'boolean',
        'sync_total_rows' => 'integer',
        'sync_processed_rows' => 'integer',
        'sync_offset' => 'integer',
    ];

    protected $appends = [
        'status_badge_text',
    ];

    public function getCredentialsJsonAttribute($value)
    {
        if (empty($value)) {
            return null;
        }
        try {
            return decrypt($value, false);
        } catch (\Throwable $e) {
            Log::error('GoogleSheetSetting: Gagal mendekripsi credentials_json. APP_KEY mungkin telah berubah atau data corrupt.', [
                'error' => $e->getMessage(),
            ]);

            return null; // kembalikan null dengan aman, jangan biarkan aplikasi crash 500
        }
    }

    public function setCredentialsJsonAttribute($value)
    {
        $this->attributes['credentials_json'] = ! empty($value) ? encrypt($value, false) : null;
    }

    public function getStatusBadgeTextAttribute(): string
    {
        return match ($this->last_sync_status) {
            'success' => 'Berhasil',
            'completed_with_errors' => 'Selesai (dengan error)',
            'failed' => 'Gagal',
            'in_progress' => 'Sedang Sinkron',
            default => 'Belum Sinkron',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleSheetSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'spreadsheet_id',
        'sheet_range',
        'credentials_json',
        'column_mapping',
        'last_sync_at',
        'last_sync_status',
        'last_sync_message',
        'is_active',
    ];

    protected $casts = [
        'credentials_json' => 'encrypted',
        'column_mapping' => 'array',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'status_badge_text',
    ];

    public function getStatusBadgeTextAttribute(): string
    {
        return match ($this->last_sync_status) {
            'success' => 'Berhasil',
            'failed' => 'Gagal',
            'in_progress' => 'Sedang Sinkron',
            default => 'Belum Sinkron',
        };
    }
}

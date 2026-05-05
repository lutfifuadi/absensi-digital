<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflineQueue extends Model
{
    use HasFactory;

    protected $table = 'offline_queues';

    protected $fillable = [
        'event_type',
        'payload',
        'device_uuid',
        'status',
        'retry_count',
        'error_message',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'synced_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(AuthorizedDevice::class, 'device_uuid', 'device_uuid');
    }
}
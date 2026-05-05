<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorizedDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_uuid',
        'device_name',
        'user_agent',
        'ip_address',
        'is_authorized',
        'last_active_at',
        'offline_mode_enabled',
        'max_retry_attempts',
    ];

    protected $casts = [
        'is_authorized' => 'boolean',
        'last_active_at' => 'datetime',
        'offline_mode_enabled' => 'boolean',
    ];
}

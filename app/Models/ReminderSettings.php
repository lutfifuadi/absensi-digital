<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderSettings extends Model
{
    use \App\Traits\HasTenant;

    use HasFactory;

    protected $table = 'reminder_settings';

    protected $fillable = [
        'reminder_type',
        'is_enabled',
        'channel',
        'send_before_minutes',
        'custom_message',
        'notify_parent',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'notify_parent' => 'boolean',
    ];
}
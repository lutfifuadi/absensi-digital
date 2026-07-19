<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaAutoreplyKeyword extends Model
{
    use HasFactory;

    protected $table = 'wa_autoreply_keywords';

    protected $fillable = [
        'keyword',
        'match_type',
        'is_validation_required',
        'is_active',
        'notification_template_type',
    ];

    protected $casts = [
        'is_validation_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke NotificationTemplate secara loose (karena relasi berdasarkan string column 'type')
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'notification_template_type', 'type');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $table = 'leave_balances';

    protected $fillable = [
        'user_id',
        'leave_limit_id',
        'period_code',
        'used_days',
        'extra_days',
        'dispensation_reason',
    ];

    /**
     * Relasi ke user pemilik saldo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke aturan limit.
     */
    public function leaveLimit(): BelongsTo
    {
        return $this->belongsTo(LeaveLimit::class);
    }
}

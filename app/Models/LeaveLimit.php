<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveLimit extends Model
{
    use HasFactory;

    protected $table = 'leave_limits';

    protected $fillable = [
        'name',
        'leave_type',
        'max_days',
        'period',
        'action_type',
        'target_roles',
        'target_grades',
        'is_active',
    ];

    protected $casts = [
        'target_roles'  => 'array',
        'target_grades' => 'array',
        'is_active'     => 'boolean',
    ];

    /**
     * Relasi ke saldo kuota per user.
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }
}

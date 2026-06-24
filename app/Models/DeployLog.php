<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeployLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'version', 'commit_hash', 'commit_message', 'status',
        'backup_path', 'log_output', 'triggered_by',
        'started_at', 'finished_at', 'duration_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function trigger()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}

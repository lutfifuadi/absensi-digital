<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityAttendance extends Model
{
    use \App\Traits\HasTenant;

    use HasFactory;

    protected $table = 'activity_attendance';

    protected $fillable = [
        'kegiatan_id',
        'siswa_id',
        'status',
        'keterangan',
        'recorded_by',
    ];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

class ActivityNotificationQueue extends Model
{
    use HasFactory;

    protected $table = 'activity_notification_queue';

    protected $fillable = [
        'kegiatan_id',
        'siswa_id',
        'notification_type',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
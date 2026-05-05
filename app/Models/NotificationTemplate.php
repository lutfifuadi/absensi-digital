<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use \App\Traits\HasTenant;

    use HasFactory;

    protected $table = 'notification_templates';

    protected $fillable = [
        'type',
        'content',
    ];

    public const TYPES = [
        'hadir_masuk' => 'Hadir Tepat Waktu',
        'terlambat_masuk' => 'Hadir Terlambat',
        'sakit_masuk' => 'Izin Sakit',
        'izin_masuk' => 'Izin Keperluan',
        'alpha_masuk' => 'Tidak Hadir (Alpha)',
        'pulang' => 'Informasi Kepulangan',
    ];
}

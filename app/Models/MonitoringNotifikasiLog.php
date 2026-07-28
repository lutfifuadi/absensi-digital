<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoringNotifikasiLog extends Model
{
    use HasFactory;

    protected $table = 'monitoring_notifikasi_log';

    protected $fillable = [
        'jadwal_pelajaran_id',
        'tanggal',
        'tipe',
        'dikirim_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'dikirim_at' => 'datetime',
    ];

    public function jadwalPelajaran()
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceAnalytics extends Model
{
    use HasFactory;

    protected $table = 'attendance_analytics';

    protected $fillable = [
        'kelas_id',
        'tahun_akademik_id',
        'date',
        'total_students',
        'hadir_tepat_waktu',
        'terlambat',
        'sakit',
        'izin',
        'alpha',
        'persentase_kehadiran',
        'persentase_keterlambatan',
        'alert_triggered',
        'alert_note',
    ];

    protected $casts = [
        'date' => 'date',
        'alert_triggered' => 'boolean',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLeaderboard extends Model
{
    use HasFactory;

    protected $table = 'student_leaderboards';

    protected $fillable = [
        'siswa_id',
        'tahun_akademik_id',
        'rank',
        'score',
        'total_attendance',
        'total_present',
        'calculated_at',
    ];

    protected $casts = [
        'calculated_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }
}

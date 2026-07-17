<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassLeaderboard extends Model
{
    use HasFactory;

    protected $table = 'class_leaderboards';

    protected $fillable = [
        'kelas_id',
        'tahun_akademik_id',
        'rank',
        'total_attendance',
        'total_present',
        'percentage',
        'calculated_at',
    ];

    protected $casts = [
        'calculated_at' => 'datetime',
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

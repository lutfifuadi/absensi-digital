<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use \App\Traits\HasTenant;

    use HasFactory;

    protected $table = 'badges';

    protected $fillable = [
        'name',
        'icon',
        'description',
        'badge_type',
        'requirement_days',
        'requirement_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function studentBadges()
    {
        return $this->hasMany(StudentBadge::class);
    }
}

class StudentBadge extends Model
{
    use HasFactory;

    protected $table = 'student_badges';

    protected $fillable = [
        'siswa_id',
        'badge_id',
        'earned_at',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }
}

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
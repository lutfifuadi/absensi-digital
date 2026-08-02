<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AbsensiPerJamStat — agregat harian absensi per jam per siswa (PRD-006, P2 — F-10).
 *
 * Satu baris = akumulasi seluruh sesi dalam 1 hari untuk 1 siswa,
 * UNIQUE (siswa_id, tanggal).
 *
 * Dipakai untuk statistik/leaderboard gamifikasi TANPA mengubah poin & streak
 * absensi harian (StudentGamificationStat tetap utuh).
 */
class AbsensiPerJamStat extends Model
{
    use HasFactory;

    protected $table = 'absensi_per_jam_stat';

    protected $fillable = [
        'siswa_id',
        'tanggal',
        'total_sesi',
        'hadir',
        'terlambat',
        'sakit',
        'izin',
        'alpha',
        'dispen',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'total_sesi'  => 'integer',
        'hadir'       => 'integer',
        'terlambat'   => 'integer',
        'sakit'       => 'integer',
        'izin'        => 'integer',
        'alpha'       => 'integer',
        'dispen'      => 'integer',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}

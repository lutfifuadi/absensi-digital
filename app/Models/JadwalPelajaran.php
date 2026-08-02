<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    use HasFactory;

    protected $table = 'jadwal_pelajaran';

    protected $fillable = [
        'kelas_id',
        'guru_id',
        'mata_pelajaran',
        'hari',
        'jam_mulai',
        'jam_selesai',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function monitoring()
    {
        return $this->hasMany(MonitoringKehadiranGuru::class, 'jadwal_pelajaran_id');
    }

    public function todayMonitoring()
    {
        return $this->hasOne(MonitoringKehadiranGuru::class, 'jadwal_pelajaran_id')
            ->where('tanggal', date('Y-m-d'));
    }

    /**
     * Catatan absensi siswa per jam untuk jadwal ini (PRD-006).
     */
    public function absensiSiswaPerJadwal()
    {
        return $this->hasMany(AbsensiSiswaPerJadwal::class, 'jadwal_pelajaran_id');
    }

    /**
     * Header sesi pencatatan untuk jadwal ini (PRD-006, P1 — F-9).
     */
    public function sesiAbsensi()
    {
        return $this->hasMany(AbsensiPerJamSesi::class, 'jadwal_pelajaran_id');
    }
}

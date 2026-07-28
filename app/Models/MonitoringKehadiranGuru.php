<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoringKehadiranGuru extends Model
{
    use HasFactory;

    protected $table = 'monitoring_kehadiran_guru';

    protected $fillable = [
        'jadwal_pelajaran_id',
        'tanggal',
        'status',
        'keterangan',
        'keterangan_lain',
        'lama_terlambat',
        'ada_pengganti',
        'guru_pengganti_id',
        'guru_pengganti_nama',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'ada_pengganti' => 'boolean',
        'lama_terlambat' => 'integer',
    ];

    public function jadwalPelajaran()
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function guruPengganti()
    {
        return $this->belongsTo(Guru::class, 'guru_pengganti_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function scopeToday($query)
    {
        return $query->where('tanggal', date('Y-m-d'));
    }

    public function scopeForTanggal($query, string $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }
}

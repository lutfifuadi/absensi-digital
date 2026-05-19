<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatKenaikanKelas extends Model
{
    use HasFactory;

    protected $table = 'riwayat_kenaikan_kelas';

    protected $fillable = [
        'siswa_id',
        'kelas_asal_id',
        'kelas_tujuan_id',
        'tahun_akademik_asal_id',
        'tahun_akademik_tujuan_id',
        'status_awal',
        'status_akhir',
        'keterangan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kelasAsal()
    {
        return $this->belongsTo(Kelas::class, 'kelas_asal_id');
    }

    public function kelasTujuan()
    {
        return $this->belongsTo(Kelas::class, 'kelas_tujuan_id');
    }

    public function tahunAkademikAsal()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_asal_id');
    }

    public function tahunAkademikTujuan()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_tujuan_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Guru;
use App\Models\TahunAkademik;
use App\Models\Siswa;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama',
        'tingkat',
        'jurusan',
        'wali_kelas_id',
        'tahun_akademik_id',
        'is_aktif_absensi',
        'kustomisasi_jam',
        'jam_masuk',
        'jam_pulang',
    ];

    protected $casts = [
        'is_aktif_absensi' => 'boolean',
        'kustomisasi_jam' => 'boolean',
    ];

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }
}

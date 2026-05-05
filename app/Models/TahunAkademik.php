<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAkademik extends Model
{
    use \App\Traits\HasTenant;

    use HasFactory;

    protected $table = 'tahun_akademik';

    protected $fillable = [
        'nama',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_aktif',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_aktif' => 'boolean',
    ];

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'tahun_akademik_id');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'tahun_akademik_id');
    }
}

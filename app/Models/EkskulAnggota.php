<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulAnggota extends Model
{
    use HasFactory;

    protected $table = 'ekskul_anggota';

    protected $fillable = [
        'ekskul_id',
        'siswa_id',
        'status',
        'tanggal_masuk',
        'tanggal_keluar',
    ];

    protected $casts = [
        'tanggal_masuk'  => 'date',
        'tanggal_keluar' => 'date',
    ];

    /**
     * Relasi ke ekskul.
     */
    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_id');
    }

    /**
     * Relasi ke siswa.
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Scope untuk anggota aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}

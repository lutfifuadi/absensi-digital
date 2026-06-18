<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulAbsensi extends Model
{
    use HasFactory;

    protected $table = 'ekskul_absensi';

    protected $fillable = [
        'ekskul_id',
        'siswa_id',
        'tanggal',
        'status',
        'jam_absen',
        'pembina_id',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
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
     * Relasi ke pembina (guru) yang mengabsen.
     */
    public function pembina()
    {
        return $this->belongsTo(Guru::class, 'pembina_id');
    }
}

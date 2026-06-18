<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ekskul extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ekskul';

    protected $fillable = [
        'nama',
        'kategori',
        'deskripsi',
        'kuota',
        'status',
        'icon',
    ];

    protected $casts = [
        'status' => 'boolean',
        'kuota'  => 'integer',
    ];

    /**
     * Relasi ke jadwal ekskul.
     */
    public function jadwal()
    {
        return $this->hasMany(EkskulJadwal::class, 'ekskul_id');
    }

    /**
     * Relasi ke pembina ekskul (many-to-many via pivot).
     */
    public function pembina()
    {
        return $this->hasMany(EkskulPembina::class, 'ekskul_id');
    }

    /**
     * Relasi ke guru (pembina) langsung via tabel pivot.
     */
    public function guruPembina()
    {
        return $this->belongsToMany(Guru::class, 'ekskul_pembina', 'ekskul_id', 'guru_id')
            ->withPivot('jabatan')
            ->withTimestamps();
    }

    /**
     * Relasi ke anggota ekskul.
     */
    public function anggota()
    {
        return $this->hasMany(EkskulAnggota::class, 'ekskul_id');
    }

    /**
     * Relasi ke absensi ekskul.
     */
    public function absensi()
    {
        return $this->hasMany(EkskulAbsensi::class, 'ekskul_id');
    }

    /**
     * Relasi ke kegiatan ekskul.
     */
    public function kegiatan()
    {
        return $this->hasMany(EkskulKegiatan::class, 'ekskul_id');
    }
}

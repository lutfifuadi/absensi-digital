<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisPelanggaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pelanggaran_jenis';

    protected $fillable = [
        'kategori_id',
        'nama',
        'deskripsi',
        'bobot_poin',
        'is_aktif',
    ];

    protected $casts = [
        'kategori_id' => 'integer',
        'bobot_poin' => 'integer',
        'is_aktif' => 'boolean',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriPelanggaran::class, 'kategori_id');
    }

    public function pelanggaranSiswa()
    {
        return $this->hasMany(PelanggaranSiswa::class, 'jenis_id');
    }
}

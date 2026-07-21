<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriPelanggaran extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran_kategori';

    protected $fillable = [
        'nama',
        'deskripsi',
        'warna',
        'urutan',
        'is_aktif',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'is_aktif' => 'boolean',
    ];

    public function jenisPelanggaran()
    {
        return $this->hasMany(JenisPelanggaran::class, 'kategori_id');
    }
}

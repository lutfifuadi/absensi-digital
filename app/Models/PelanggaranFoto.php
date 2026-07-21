<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelanggaranFoto extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran_foto';

    public $timestamps = false;

    protected $fillable = [
        'pelanggaran_id',
        'path_foto',
        'nama_file_asli',
        'ukuran_byte',
        'created_at',
    ];

    protected $casts = [
        'pelanggaran_id' => 'integer',
        'ukuran_byte' => 'integer',
        'created_at' => 'datetime',
    ];

    public function pelanggaranSiswa()
    {
        return $this->belongsTo(PelanggaranSiswa::class, 'pelanggaran_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomdisSanksi extends Model
{
    use HasFactory;

    protected $table = 'komdis_sanksi';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function sidang()
    {
        return $this->belongsTo(KomdisSidang::class, 'komdis_sidang_id');
    }

    public function diberikanOleh()
    {
        return $this->belongsTo(Guru::class, 'diberikan_oleh');
    }
}

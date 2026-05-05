<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAkademik extends Model
{
    use HasFactory;

    protected $table = 'tahun_akademik';

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'tahun_akademik_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomdisSidang extends Model
{
    use HasFactory;

    protected $table = 'komdis_sidang';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_sidang' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function pimpinanSidang()
    {
        return $this->belongsTo(Guru::class, 'pimpinan_sidang_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sanksi()
    {
        return $this->hasMany(KomdisSanksi::class, 'komdis_sidang_id');
    }

    public function kasusBk()
    {
        return $this->belongsTo(BkKasus::class, 'bk_kasus_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BkKasus extends Model
{
    use HasFactory;

    protected $table = 'bk_kasus';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_lapor' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function konselor()
    {
        return $this->belongsTo(Guru::class, 'guru_bk_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logKonseling()
    {
        return $this->hasMany(BkLogKonseling::class, 'bk_kasus_id');
    }

    public function sidang()
    {
        return $this->hasMany(KomdisSidang::class, 'bk_kasus_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use \App\Traits\HasTenant;

    use HasFactory;

    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nip',
        'nama_lengkap',
        'jenis_kelamin',
        'mata_pelajaran',
        'jabatan',
        'no_hp',
        'foto',
        'status',
        'qr_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelasSebagaiWali()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }
}

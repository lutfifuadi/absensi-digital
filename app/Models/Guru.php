<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
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
        'qr_code_nip',
        'is_guru_bk',
        'konseling_limit',
    ];

    protected $casts = [
        'is_guru_bk' => 'boolean',
        'konseling_limit' => 'integer',
    ];

    public function scopeGuruBk($query)
    {
        return $query->where('is_guru_bk', true);
    }

    public function getNamaAttribute()
    {
        return $this->attributes['nama_lengkap'] ?? null;
    }

    public function setNoHpAttribute($value): void
    {
        $this->attributes['no_hp'] = \App\Helpers\WhatsAppHelper::formatNumber($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mapels()
    {
        return $this->belongsToMany(Mapel::class, 'guru_mapel', 'guru_id', 'mapel_id')->withTimestamps();
    }

    public function kelasSebagaiWali()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    public function absensi()
    {
        return $this->hasMany(AbsensiGuru::class, 'guru_id');
    }

    public function absensiGuru()
    {
        return $this->hasMany(AbsensiGuru::class, 'guru_id');
    }
}

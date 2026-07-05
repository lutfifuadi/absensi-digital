<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'ortu_user_id',
        'nis',
        'nisn',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'no_hp_ortu',
        'foto',
        'kelas_id',
        'tahun_akademik_id',
        'status',
        'qr_code',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Siswa $siswa) {
            $siswa->absensi()->delete();
            $siswa->absensiKegiatan()->delete();
            $siswa->izinSakit()->delete();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function absensi()
    {
        return $this->hasMany(AbsensiSiswa::class, 'siswa_id');
    }

    public function absensiKegiatan()
    {
        return $this->hasMany(AbsensiKegiatan::class, 'siswa_id');
    }

    public function izinSakit()
    {
        return $this->hasMany(IzinSakit::class, 'reference_id')->where('tipe', 'siswa');
    }

    public function ortu()
    {
        return $this->belongsToMany(User::class, 'siswa_ortu', 'siswa_id', 'ortu_user_id')->withTimestamps();
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    public function studentBadges()
    {
        return $this->hasMany(StudentBadge::class, 'siswa_id');
    }

    public function studentLeaderboard()
    {
        return $this->hasMany(StudentLeaderboard::class, 'siswa_id');
    }

    public function riwayatKenaikanKelas()
    {
        return $this->hasMany(RiwayatKenaikanKelas::class, 'siswa_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalKegiatan extends Model
{
    use HasFactory;

    protected $table = 'jadwal_kegiatan';

    protected $fillable = [
        'nama_kegiatan',
        'jenis',
        'tipe_jadwal',
        'hari',
        'tanggal_kalender',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'keterangan',
        'is_wajib',
        'target_peserta',
        'target_tingkat',
        'target_jurusan',
        'target_gender',
        'target_siswa',
        'ekskul_id',
        'ekskul_jadwal_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'qr_code_prefix',
        'is_aktif',
        'tahun_akademik_id',
    ];

    protected $casts = [
        'hari' => 'array',
        'tanggal_kalender' => 'array',
        'target_peserta' => 'array',
        'target_tingkat' => 'array',
        'target_jurusan' => 'array',
        'target_siswa' => 'array',
        'is_wajib' => 'boolean',
        'is_aktif' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function kegiatans()
    {
        return $this->hasMany(Kegiatan::class, 'jadwal_kegiatan_id');
    }

    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_id');
    }

    public function ekskulJadwal()
    {
        return $this->belongsTo(EkskulJadwal::class, 'ekskul_jadwal_id');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{

    use HasFactory;

    protected $table = 'kegiatan';

    protected $fillable = [
        'nama_kegiatan',
        'jenis',
        'tanggal_pelaksanaan',
        'tanggal_selesai',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'keterangan',
        'qr_code_kegiatan',
        'is_wajib',
        'target_peserta',
        'target_tingkat',
        'target_jurusan',
        'tahun_akademik_id',
    ];

    protected $casts = [
        'tanggal_pelaksanaan' => 'date',
        'tanggal_selesai' => 'date',
        'is_wajib' => 'boolean',
        'target_peserta' => 'array',
        'target_tingkat' => 'array',
        'target_jurusan' => 'array',
    ];

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    public function absensiKegiatan()
    {
        return $this->hasMany(AbsensiKegiatan::class, 'kegiatan_id');
    }
}

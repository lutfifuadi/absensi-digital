<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PelanggaranSiswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pelanggaran_siswa';

    protected $fillable = [
        'siswa_id',
        'jenis_id',
        'tahun_akademik_id',
        'tanggal_kejadian',
        'keterangan',
        'poin_saat_itu',
        'dicatat_oleh',
        'is_diarsipkan',
    ];

    protected $casts = [
        'siswa_id' => 'integer',
        'jenis_id' => 'integer',
        'tahun_akademik_id' => 'integer',
        'tanggal_kejadian' => 'date',
        'poin_saat_itu' => 'integer',
        'dicatat_oleh' => 'integer',
        'is_diarsipkan' => 'boolean',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function jenisPelanggaran()
    {
        return $this->belongsTo(JenisPelanggaran::class, 'jenis_id');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function fotos()
    {
        return $this->hasMany(PelanggaranFoto::class, 'pelanggaran_id');
    }

    public function notifLogs()
    {
        return $this->hasMany(PelanggaranNotifLog::class, 'pelanggaran_id');
    }
}

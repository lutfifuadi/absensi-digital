<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelanggaranSp extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran_sp';

    protected $fillable = [
        'siswa_id',
        'tahun_akademik_id',
        'level_sp',
        'total_poin_saat_sp',
        'tanggal_sp',
        'catatan_tambahan',
        'diterbitkan_oleh',
    ];

    protected $casts = [
        'siswa_id' => 'integer',
        'tahun_akademik_id' => 'integer',
        'total_poin_saat_sp' => 'integer',
        'tanggal_sp' => 'date',
        'diterbitkan_oleh' => 'integer',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    public function penerbit()
    {
        return $this->belongsTo(User::class, 'diterbitkan_oleh');
    }

    public function notifLogs()
    {
        return $this->hasMany(PelanggaranNotifLog::class, 'sp_id');
    }
}

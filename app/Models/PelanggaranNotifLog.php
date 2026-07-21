<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelanggaranNotifLog extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran_notif_log';

    protected $fillable = [
        'pelanggaran_id',
        'sp_id',
        'siswa_id',
        'penerima_no_hp',
        'tipe_notif',
        'status',
        'pesan',
        'respons_gateway',
        'dikirim_pada',
    ];

    protected $casts = [
        'pelanggaran_id' => 'integer',
        'sp_id' => 'integer',
        'siswa_id' => 'integer',
        'dikirim_pada' => 'datetime',
    ];

    public function pelanggaranSiswa()
    {
        return $this->belongsTo(PelanggaranSiswa::class, 'pelanggaran_id');
    }

    public function pelanggaranSp()
    {
        return $this->belongsTo(PelanggaranSp::class, 'sp_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}

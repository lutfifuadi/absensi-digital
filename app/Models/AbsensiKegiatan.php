<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiKegiatan extends Model
{
    use HasFactory;

    protected $table = 'absensi_kegiatan';

    protected $fillable = [
        'kegiatan_id',
        'siswa_id',
        'jam_absen',
        'status',
        'keterangan',
        'foto_bukti',
    ];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}

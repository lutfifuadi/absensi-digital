<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelanggaranPemutihanLog extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran_pemutihan_log';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_pemutihan' => 'date',
        'poin_sebelum' => 'integer',
        'poin_sesudah' => 'integer',
        'poin_yang_diputihkan' => 'integer',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function diprosesOleh()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}

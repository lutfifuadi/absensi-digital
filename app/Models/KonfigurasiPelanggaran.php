<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonfigurasiPelanggaran extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran_konfigurasi';

    protected $fillable = [
        'tahun_akademik_id',
        'batas_sp1',
        'batas_sp2',
        'batas_sp3',
        'notif_wa_aktif',
        'created_by',
    ];

    protected $casts = [
        'tahun_akademik_id' => 'integer',
        'batas_sp1' => 'integer',
        'batas_sp2' => 'integer',
        'batas_sp3' => 'integer',
        'notif_wa_aktif' => 'boolean',
        'created_by' => 'integer',
    ];

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

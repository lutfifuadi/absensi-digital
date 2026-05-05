<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IzinSakit extends Model
{
    use \App\Traits\HasTenant;

    use HasFactory;

    protected $table = 'izin_sakit';

    protected $fillable = [
        'tipe',
        'reference_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'keterangan',
        'lampiran',
        'status',
        'disetujui_oleh',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'reference_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'reference_id');
    }

    public function staff()
    {
        return $this->belongsTo(StaffTataUsaha::class, 'reference_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}

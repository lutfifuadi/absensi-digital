<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IzinSakit extends Model
{
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
        'is_overlimit',
        'overlimit_reason',
        'is_dispensation',
        'user_id',
    ];

    protected $casts = [
        'tanggal_mulai'  => 'date',
        'tanggal_selesai' => 'date',
        'is_overlimit'    => 'boolean',
        'is_dispensation' => 'boolean',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'reference_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'reference_id');
    }

    public function staff()
    {
        return $this->belongsTo(StaffTataUsaha::class, 'reference_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * User yang mengajukan izin (pelaku login).
     */
    public function pengaju()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

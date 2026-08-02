<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiStaff extends Model
{
    use HasFactory;

    protected $table = 'absensi_staff';

    protected $fillable = [
        'staff_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
        'keterangan',
        'metode',
        'is_pulang_cepat',
        'izin_pulang_cepat_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(StaffTataUsaha::class, 'staff_id');
    }

    public function izinPulangCepat()
    {
        return $this->belongsTo(IzinPulangCepat::class, 'izin_pulang_cepat_id');
    }
}

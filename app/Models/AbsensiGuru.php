<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\AbsensiGuruObserver;

#[ObservedBy(AbsensiGuruObserver::class)]
class AbsensiGuru extends Model
{
    use HasFactory;

    protected $table = 'absensi_guru';

    protected $fillable = [
        'guru_id',
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

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function izinPulangCepat()
    {
        return $this->belongsTo(IzinPulangCepat::class, 'izin_pulang_cepat_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $table = 'holidays';

    protected $fillable = [
        'tanggal',
        'nama',
        'jenis',
        'is_national_holiday',
        'tingkat',
        'kelas_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_national_holiday' => 'boolean',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public static function isSiswaHoliday(Siswa $siswa, string $tanggal): bool
    {
        $tingkat = $siswa->kelas?->tingkat;
        $kelasId = $siswa->kelas_id;

        return self::whereDate('tanggal', $tanggal)
            ->where(function ($query) use ($tingkat, $kelasId) {
                $query->where(function ($q) {
                    $q->whereNull('tingkat')->whereNull('kelas_id');
                });

                if ($tingkat) {
                    $query->orWhere(function ($q) use ($tingkat) {
                        $q->where('tingkat', $tingkat)->whereNull('kelas_id');
                    });
                }

                if ($kelasId) {
                    $query->orWhere(function ($q) use ($kelasId) {
                        $q->where('kelas_id', $kelasId);
                    });
                }
            })
            ->exists();
    }
}
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
        'batch_id',
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
        $tingkat = $siswa->kelas?->tingkat ?? 'none';
        $kelasId = $siswa->kelas_id ?? 0;
        $cacheKey = "holiday_siswa_{$tanggal}_{$tingkat}_{$kelasId}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($tanggal, $tingkat, $kelasId) {
            $tingkatVal = $tingkat === 'none' ? null : $tingkat;
            $kelasIdVal = $kelasId === 0 ? null : $kelasId;

            return self::whereDate('tanggal', $tanggal)
                ->where(function ($query) use ($tingkatVal, $kelasIdVal) {
                    $query->where(function ($q) {
                        $q->whereNull('tingkat')->whereNull('kelas_id');
                    });

                    if ($tingkatVal) {
                        $query->orWhere(function ($q) use ($tingkatVal) {
                            $q->where('tingkat', $tingkatVal)->whereNull('kelas_id');
                        });
                    }

                    if ($kelasIdVal) {
                        $query->orWhere(function ($q) use ($kelasIdVal) {
                            $q->where('kelas_id', $kelasIdVal);
                        });
                    }
                })
                ->exists();
        });
    }
}
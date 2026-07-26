<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KelasJadwalAbsensi extends Model
{
    use HasFactory;

    protected $table = 'kelas_jadwal_absensi';

    protected $fillable = [
        'kelas_id',
        'hari',
        'jam_mulai_absensi',
        'jam_masuk',
        'batas_jam_masuk',
        'jam_pulang',
        'jam_akhir_pulang',
        'is_libur',
    ];

    /**
     * Cast tipe data kolom.
     *
     * Field waktu di-cast ke format H:i untuk konsistensi
     * dengan pola yang sudah ada di project.
     */
    protected $casts = [
        'jam_mulai_absensi' => 'datetime:H:i',
        'jam_masuk' => 'datetime:H:i',
        'batas_jam_masuk' => 'datetime:H:i',
        'jam_pulang' => 'datetime:H:i',
        'jam_akhir_pulang' => 'datetime:H:i',
        'is_libur' => 'boolean',
    ];

    /**
     * Relasi ke tabel kelas.
     * Setiap jadwal absensi dimiliki oleh satu kelas.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }
}

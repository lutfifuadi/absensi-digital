<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AbsensiPerJamNotifLog — log notifikasi absensi per jam (PRD-006, P1 — F-6).
 *
 * Mencatat setiap pengiriman notifikasi (WA / in-app) per catatan absensi.
 * UNIQUE (absensi_per_jadwal_id, jenis) menjamin anti-spam:
 * sekali WA & sekali in-app per catatan kehadiran siswa.
 *
 * Log bersifat immutable — hanya created_at, tanpa updated_at.
 */
class AbsensiPerJamNotifLog extends Model
{
    use HasFactory;

    protected $table = 'absensi_per_jam_notif_log';

    /**
     * Tabel hanya memiliki created_at (tanpa updated_at).
     */
    public $timestamps = false;

    protected $fillable = [
        'absensi_per_jadwal_id',
        'siswa_id',
        'tanggal',
        'jenis',        // wa | in_app
        'status',       // snapshot status kehadiran saat notifikasi dikirim
        'status_kirim', // sent | skipped | failed
        'alasan',
        'created_at',
    ];

    protected $casts = [
        'tanggal'    => 'date',
        'created_at' => 'datetime',
    ];

    public function absensiPerJadwal(): BelongsTo
    {
        return $this->belongsTo(AbsensiSiswaPerJadwal::class, 'absensi_per_jadwal_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}

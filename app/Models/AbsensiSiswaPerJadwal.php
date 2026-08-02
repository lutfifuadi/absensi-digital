<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AbsensiSiswaPerJadwal — data inti absensi siswa per jam pelajaran (PRD-006, P0).
 *
 * Satu baris = 1 catatan kehadiran siswa pada 1 sesi (jadwal_pelajaran_id + tanggal).
 * UNIQUE (jadwal_pelajaran_id, siswa_id, tanggal) → anti-duplikat BR-01.
 * kelas_id adalah kolom denormalisasi dari jadwal_pelajaran.kelas_id (dari Kang Encep).
 */
class AbsensiSiswaPerJadwal extends Model
{
    use HasFactory;

    protected $table = 'absensi_siswa_per_jadwal';

    protected $fillable = [
        'jadwal_pelajaran_id',
        'siswa_id',
        'kelas_id',
        'tanggal',
        'status',
        'lama_terlambat',
        'keterangan',
        'metode',
        'dicatat_oleh',
        'is_pulang_cepat',
        'izin_pulang_cepat_id',
    ];

    protected $casts = [
        'tanggal'        => 'date',
        'status'         => 'string', // hadir|terlambat|sakit|izin|alpha|dispen
        'lama_terlambat' => 'integer',
    ];

    public function jadwalPelajaran(): BelongsTo
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Pengisi / pencatat (audit trail BR-10).
     */
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    /**
     * Log notifikasi (P1, F-6) — anti-spam WA/in-app.
     */
    public function notifLogs(): HasMany
    {
        return $this->hasMany(AbsensiPerJamNotifLog::class, 'absensi_per_jadwal_id');
    }

    public function izinPulangCepat(): BelongsTo
    {
        return $this->belongsTo(IzinPulangCepat::class, 'izin_pulang_cepat_id');
    }
}

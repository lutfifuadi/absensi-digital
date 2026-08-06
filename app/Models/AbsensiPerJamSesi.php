<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AbsensiPerJamSesi — header sesi pencatatan absensi per jam (PRD-006, P1 — F-9).
 *
 * Satu baris = 1 sesi (jadwal_pelajaran_id + tanggal), UNIQUE (jadwal_pelajaran_id, tanggal).
 * Menyimpan ringkasan & status kelengkapan agar dashboard piket/admin dapat
 * mendeteksi "jam mana yang belum diisi" hari ini secara efisien.
 */
class AbsensiPerJamSesi extends Model
{
    use HasFactory;

    protected $table = 'absensi_per_jam_sesi';

    protected $fillable = [
        'jadwal_pelajaran_id',
        'kelas_id',
        'tanggal',
        'guru_id',
        'dicatat_oleh',
        'jumlah_siswa',
        'jumlah_hadir',
        'jumlah_alpha',
        'materi',
        'catatan',
    ];

    protected $casts = [
        'tanggal'       => 'date',
        'jumlah_siswa'  => 'integer',
        'jumlah_hadir'  => 'integer',
        'jumlah_alpha'  => 'integer',
    ];

    public function jadwalPelajaran(): BelongsTo
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Guru pengampu saat sesi berlangsung (bisa guru pengganti). Nullable.
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    /**
     * Pengisi pertama yang membuat header sesi.
     */
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}

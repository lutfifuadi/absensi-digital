<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IzinPulangCepat extends Model
{
    use HasFactory;

    protected $table = 'izin_pulang_cepat';

    protected $fillable = [
        'kode_izin',
        'kategori',
        'reference_id',
        'user_id',
        'tanggal',
        'jam_rencana_keluar',
        'jam_realisasi_keluar',
        'alasan',
        'jenis_alasan',
        'lampiran',
        'nama_penjemput',
        'no_hp_penjemput',
        'status',
        'disetujui_oleh',
        'disetujui_pada',
        'catatan_approver',
        'diverifikasi_satpam_oleh',
        'diverifikasi_satpam_pada',
    ];

    protected $casts = [
        'tanggal'                  => 'date',
        'jam_rencana_keluar'       => 'string',
        'jam_realisasi_keluar'     => 'string',
        'disetujui_pada'           => 'datetime',
        'diverifikasi_satpam_pada' => 'datetime',
    ];

    /**
     * Relasi ke entitas pengaju (Siswa, Guru, atau Staff).
     */
    public function reference()
    {
        return match ($this->kategori) {
            'siswa' => $this->belongsTo(Siswa::class, 'reference_id'),
            'guru'  => $this->belongsTo(Guru::class, 'reference_id'),
            'staff' => $this->belongsTo(StaffTataUsaha::class, 'reference_id'),
            default => $this->belongsTo(Siswa::class, 'reference_id'),
        };
    }

    /**
     * User pembuat/pengaju permohonan izin.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * User yang menyetujui (Approver).
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * User satpam yang memverifikasi kepulangan.
     */
    public function satpam(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_satpam_oleh');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengaduan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pengaduan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_unik',
        'user_id',
        'siswa_id',
        'kelas_id',
        'nama_lengkap',
        'status_pelapor',
        'kategori',
        'deskripsi',
        'nomor_wa',
        'status',
        'catatan_admin',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string',
        'verified_at' => 'datetime',
    ];

    public function setNomorWaAttribute($value): void
    {
        $this->attributes['nomor_wa'] = \App\Helpers\WhatsAppHelper::formatNumber($value);
    }

    /**
     * Get the logs for this pengaduan.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(LogPengaduan::class, 'pengaduan_id');
    }

    /**
     * Get the kelas associated with the pengaduan.
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Get the siswa associated with the pengaduan.
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Get the user/admin associated with the pengaduan.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the status label in Indonesian.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'baru' => 'Baru',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the status color for badge.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'baru' => 'warning',
            'diproses' => 'info',
            'selesai' => 'success',
            'ditolak' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the resolved class name (Nama Kelas) for the pengaduan.
     */
    public function getNamaKelasAttribute(): ?string
    {
        if ($this->kelas && $this->kelas->nama) {
            return $this->kelas->nama;
        }

        if ($this->siswa && $this->siswa->kelas && $this->siswa->kelas->nama) {
            return $this->siswa->kelas->nama;
        }

        if ($this->user && $this->user->siswa && $this->user->siswa->kelas && $this->user->siswa->kelas->nama) {
            return $this->user->siswa->kelas->nama;
        }

        // Fallback: search Siswa by nomor_wa or nama_lengkap
        if (!empty($this->nomor_wa) || !empty($this->nama_lengkap)) {
            $siswa = \App\Models\Siswa::query()
                ->where(function ($q) {
                    if ($this->nomor_wa) {
                        $waFormatted = \App\Helpers\WhatsAppHelper::formatNumber($this->nomor_wa);
                        $q->where('no_hp', $waFormatted)->orWhere('no_hp_ortu', $waFormatted);
                    }
                    if ($this->nama_lengkap) {
                        $q->orWhere('nama_lengkap', 'LIKE', $this->nama_lengkap);
                    }
                })
                ->with('kelas')
                ->first();

            if ($siswa && $siswa->kelas && $siswa->kelas->nama) {
                return $siswa->kelas->nama;
            }
        }

        return null;
    }
}

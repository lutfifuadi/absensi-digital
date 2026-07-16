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

    /**
     * Get the logs for this pengaduan.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(LogPengaduan::class, 'pengaduan_id');
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
}

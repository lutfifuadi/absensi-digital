<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianLisensi extends Model
{
    use HasFactory;

    protected $table = 'pembelian_lisensi';

    protected $fillable = [
        'nama_klien',
        'email_klien',
        'domain',
        'license_key',
        'status',
        'payment_status',
        'download_token',
        'catatan',
        'activated_at',
        'expires_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    /**
     * Generate a secure license key in format PRE-XXXX-XXXX-XXXX-XXXX
     */
    public static function generateLicenseKey(): string
    {
        $segments = [];
        for ($i = 0; $i < 4; $i++) {
            $segments[] = strtoupper(bin2hex(random_bytes(2)));
        }
        return 'PRE-' . implode('-', $segments);
    }

    /**
     * Generate a secure download token
     */
    public static function generateDownloadToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Check if the license is currently valid
     */
    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Scope: only active/valid licenses
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

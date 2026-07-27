<?php

namespace App\Services;

use App\Models\Pengaturan;

class PengaturanService
{
    /**
     * Baca nilai toggle dari tabel pengaturan, bypass cache.
     */
    public function getToggleValue(string $key): string
    {
        return Pengaturan::where('key', $key)->value('value') ?? 'Ya';
    }

    /**
     * Cek apakah fitur auto-alpha siswa aktif.
     */
    public function isAutoAlphaEnabled(): bool
    {
        return $this->getToggleValue('auto_alpha_siswa_enabled') === 'Ya';
    }

    /**
     * Cek apakah notifikasi WA auto-alpha aktif.
     */
    public function isAutoAlphaWaNotifEnabled(): bool
    {
        return $this->getToggleValue('auto_alpha_wa_notif') === 'Ya';
    }
}

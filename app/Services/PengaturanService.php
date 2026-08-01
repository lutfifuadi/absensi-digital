<?php

namespace App\Services;

class PengaturanService
{
    protected SettingsManager $settings;

    public function __construct(SettingsManager $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Baca nilai toggle dari tabel pengaturan (via SettingsManager cache).
     */
    public function getToggleValue(string $key): string
    {
        return $this->settings->getBool($key) ? 'Ya' : 'Tidak';
    }

    /**
     * Cek apakah fitur auto-alpha siswa aktif.
     */
    public function isAutoAlphaEnabled(): bool
    {
        return $this->settings->getBool('auto_alpha_siswa_enabled');
    }

    /**
     * Cek apakah notifikasi WA auto-alpha aktif.
     */
    public function isAutoAlphaWaNotifEnabled(): bool
    {
        return $this->settings->getBool('auto_alpha_wa_notif');
    }
}

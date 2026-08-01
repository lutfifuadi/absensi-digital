<?php

namespace App\Services;

use App\Exceptions\FeatureDisabledException;
use App\Support\PengaturanDefaults;

class FeatureManager
{
    protected SettingsManager $settings;

    public function __construct(SettingsManager $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Cek apakah suatu fitur dalam kondisi AKTIF (ON).
     */
    public function isOn(string $key): bool
    {
        return $this->settings->getBool($key);
    }

    /**
     * Cek apakah suatu fitur dalam kondisi NON-AKTIF (OFF).
     */
    public function isOff(string $key): bool
    {
        return !$this->isOn($key);
    }

    /**
     * Guard: Lempar exception jika fitur NON-AKTIF.
     *
     * @throws FeatureDisabledException
     */
    public function guard(string $key): void
    {
        if ($this->isOff($key)) {
            $meta = PengaturanDefaults::get($key);
            $label = $meta['label'] ?? $key;
            throw new FeatureDisabledException("Fitur \"{$label}\" sedang dinonaktifkan oleh administrator.");
        }
    }

    /**
     * Mengembalikan seluruh daftar feature toggle beserta status aktif/non-aktif saat ini.
     */
    public function list(): array
    {
        $toggles = PengaturanDefaults::toggleFeatures();
        $result = [];

        foreach ($toggles as $key => $meta) {
            $meta['is_on'] = $this->isOn($key);
            $result[$key] = $meta;
        }

        return $result;
    }
}

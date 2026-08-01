<?php

use App\Facades\Feature;
use App\Services\SettingsManager;

if (!function_exists('setting')) {
    /**
     * Dapatkan nilai pengaturan aplikasi.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        /** @var SettingsManager $manager */
        $manager = app(SettingsManager::class);
        return $manager->get($key, $default);
    }
}

if (!function_exists('feature')) {
    /**
     * Cek apakah suatu fitur aktif.
     */
    function feature(string $key): bool
    {
        return Feature::isOn($key);
    }
}

if (!function_exists('feature_is_on')) {
    /**
     * Alias untuk feature().
     */
    function feature_is_on(string $key): bool
    {
        return Feature::isOn($key);
    }
}

if (!function_exists('feature_is_off')) {
    /**
     * Cek apakah suatu fitur non-aktif.
     */
    function feature_is_off(string $key): bool
    {
        return Feature::isOff($key);
    }
}

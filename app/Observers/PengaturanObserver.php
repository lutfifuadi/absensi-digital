<?php

namespace App\Observers;

use App\Models\Pengaturan;
use App\Services\SettingsManager;

class PengaturanObserver
{
    protected SettingsManager $settings;

    public function __construct(SettingsManager $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Handle the Pengaturan "created" event.
     */
    public function created(Pengaturan $pengaturan): void
    {
        $this->settings->forget($pengaturan->key);
    }

    /**
     * Handle the Pengaturan "updated" event.
     */
    public function updated(Pengaturan $pengaturan): void
    {
        $this->settings->forget($pengaturan->key);
    }

    /**
     * Handle the Pengaturan "deleted" event.
     */
    public function deleted(Pengaturan $pengaturan): void
    {
        $this->settings->forget($pengaturan->key);
    }
}

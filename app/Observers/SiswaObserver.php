<?php

namespace App\Observers;

use App\Jobs\SyncToGoogleSheetJob;
use App\Models\Siswa;

class SiswaObserver
{
    /**
     * Handle the Siswa "created" event.
     */
    public function created(Siswa $siswa): void
    {
        SyncToGoogleSheetJob::dispatch($siswa, 'created', 'siswa');
    }

    /**
     * Handle the Siswa "updated" event.
     */
    public function updated(Siswa $siswa): void
    {
        SyncToGoogleSheetJob::dispatch($siswa, 'updated', 'siswa');
    }

    /**
     * Handle the Siswa "deleted" event.
     */
    public function deleted(Siswa $siswa): void
    {
        SyncToGoogleSheetJob::dispatch($siswa, 'deleted', 'siswa');
    }
}

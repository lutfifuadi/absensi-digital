<?php

namespace App\Observers;

use App\Jobs\SyncToGoogleSheetJob;
use App\Models\Guru;

class GuruObserver
{
    /**
     * Handle the Guru "created" event.
     */
    public function created(Guru $guru): void
    {
        SyncToGoogleSheetJob::dispatch($guru, 'created', 'guru');
    }

    /**
     * Handle the Guru "updated" event.
     */
    public function updated(Guru $guru): void
    {
        SyncToGoogleSheetJob::dispatch($guru, 'updated', 'guru');
    }

    /**
     * Handle the Guru "deleted" event.
     */
    public function deleted(Guru $guru): void
    {
        SyncToGoogleSheetJob::dispatch($guru, 'deleted', 'guru');
    }
}

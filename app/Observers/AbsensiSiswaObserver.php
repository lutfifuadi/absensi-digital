<?php

namespace App\Observers;

use App\Models\AbsensiSiswa;
use App\Jobs\HitungPoinGamifikasiJob;
use App\Jobs\ProcessAbsensiNotificationJob;

class AbsensiSiswaObserver
{
    /**
     * Handle the AbsensiSiswa "created" event.
     */
    public function created(AbsensiSiswa $absensiSiswa): void
    {
        ProcessAbsensiNotificationJob::dispatch($absensiSiswa, 'created');

        HitungPoinGamifikasiJob::dispatch($absensiSiswa);

        \App\Jobs\SyncToGoogleSheetJob::dispatch($absensiSiswa, 'created', 'absensi_siswa');
    }

    /**
     * Handle the AbsensiSiswa "updated" event.
     */
    public function updated(AbsensiSiswa $absensiSiswa): void
    {
        if ($absensiSiswa->isDirty('jam_pulang') && !empty($absensiSiswa->jam_pulang)) {
            ProcessAbsensiNotificationJob::dispatch($absensiSiswa, 'updated');
        }

        \App\Jobs\SyncToGoogleSheetJob::dispatch($absensiSiswa, 'updated', 'absensi_siswa');
    }
}

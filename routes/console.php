<?php

use App\Console\Commands\AutoMarkAlphaCommand;
use App\Console\Commands\SyncMasterData;
use App\Models\Pengaturan;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal: setiap hari jam 08.00 tandai alpha bagi yang belum absen
Schedule::command(AutoMarkAlphaCommand::class)->dailyAt('08:00');

// Jadwal: setiap menit cek lisensi ke server untuk antisipasi lisensi ilegal
Schedule::command(\App\Console\Commands\VerifyLicense::class)->everyMinute()->withoutOverlapping();

Schedule::call(function () {
    \Illuminate\Support\Facades\Cache::put('queue_heartbeat', now(), 120);
})->everyMinute()->description('Queue Heartbeat');

Schedule::command('model:prune', ['--model' => \App\Models\DeployLog::class])->daily();

if (file_exists(storage_path('installed'))) {
    try {
        $syncEnabled = Pengaturan::where('key', 'master_db_sync_enabled')->value('value') ?? 'Ya';
        $syncMode    = Pengaturan::where('key', 'master_db_sync_mode')->value('value') ?? 'otomatis';

        if (strtolower($syncEnabled) === 'ya' && strtolower($syncMode) === 'otomatis') {
            $syncTime = Pengaturan::where('key', 'master_db_sync_time')->value('value') ?? '03:00';
            if (!preg_match('/^\d{2}:\d{2}$/', $syncTime)) {
                $syncTime = '03:00';
            }

            // Jadwal sinkronisasi harian data siswa dan user dari API eksternal
            Schedule::command(SyncMasterData::class)
                ->dailyAt($syncTime)
                ->withoutOverlapping();
        }
    } catch (\Exception $e) {
        // Silently fail if database is not ready
    }
}

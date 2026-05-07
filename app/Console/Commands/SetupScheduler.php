<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SetupScheduler extends Command
{
    protected $signature = 'license:install-scheduler';
    protected $description = 'Automatically register the Laravel scheduler in the system (Windows Task Scheduler or Linux Cron)';

    public function handle()
    {
        $this->info('Starting automatic scheduler installation...');

        if (PHP_OS_FAMILY === 'Windows') {
            return $this->setupWindows();
        } else {
            return $this->setupLinux();
        }
    }

    private function setupWindows()
    {
        $phpBinary = PHP_BINARY;
        $artisanPath = base_path('artisan');
        $taskName = 'AbsensiPusatScheduler';
        
        // Command to run the scheduler every minute
        // /sc minute /mo 1 = every 1 minute
        // /f = force overwrite if exists
        $command = "schtasks /create /sc minute /mo 1 /tn \"{$taskName}\" /tr \"{$phpBinary} {$artisanPath} schedule:run\" /f";

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $this->info('Successfully registered Windows Task Scheduler.');
            Log::info('Scheduler installed successfully on Windows.');
            return 0;
        } else {
            $this->error('Failed to register Windows Task Scheduler. Error code: ' . $returnVar);
            Log::error('Failed to install Windows Scheduler. Output: ' . implode("\n", $output));
            return 1;
        }
    }

    private function setupLinux()
    {
        $phpBinary = PHP_BINARY;
        $artisanPath = base_path('artisan');
        $cronJob = "* * * * * {$phpBinary} {$artisanPath} schedule:run >> /dev/null 2>&1";

        // Check if already exists to avoid duplicates
        $currentCron = shell_exec('crontab -l 2>/dev/null');
        
        if (str_contains($currentCron, $artisanPath)) {
            $this->info('Scheduler already exists in crontab.');
            return 0;
        }

        // Add to crontab
        $newCron = ($currentCron ? rtrim($currentCron) . PHP_EOL : '') . $cronJob . PHP_EOL;
        file_put_contents(storage_path('app/temp_cron'), $newCron);
        
        exec('crontab ' . storage_path('app/temp_cron'));
        unlink(storage_path('app/temp_cron'));

        $this->info('Successfully registered Linux Crontab.');
        Log::info('Scheduler installed successfully on Linux.');
        return 0;
    }
}

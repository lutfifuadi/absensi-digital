<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LogsClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan file storage/logs/laravel.log';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            $this->warn("File log tidak ditemukan: {$logPath}");
            return self::SUCCESS;
        }

        try {
            // Kosongkan file dengan LOCK_EX untuk keamanan (write lock)
            $bytes = file_put_contents($logPath, '', LOCK_EX);

            if ($bytes === false) {
                $this->error("Gagal membersihkan file log: {$logPath}");
                return self::FAILURE;
            }

            $this->info("File log berhasil dibersihkan: {$logPath}");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Gagal membersihkan file log: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}

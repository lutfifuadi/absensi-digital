<?php

namespace App\Console\Commands;

use App\Jobs\BelumMonitorNotificationJob;
use Illuminate\Console\Command;

class CheckBelumMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:check-belum-monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengecek jadwal pelajaran yang belum dimonitor kehadirannya dan mengirim notifikasi jika sudah lewat 15 menit dari jam mulai.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mengecek jadwal yang belum dimonitor...');
        BelumMonitorNotificationJob::dispatch();
        $this->info('Job pengecekan telah didispatch.');
    }
}

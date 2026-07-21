<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WaAutoreplyLog;
use Illuminate\Support\Facades\Log;

class CleanWaAutoreplyLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wa-autoreply:clean-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus log autoreply yang sudah lebih dari 24 jam';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoff = now()->subDay(); // 24 jam yang lalu

        $deleted = WaAutoreplyLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Berhasil menghapus {$deleted} log autoreply yang lebih dari 24 jam.");
        
        Log::info("CleanWaAutoreplyLogs: {$deleted} log dihapus (lebih dari 24 jam).");
    }
}

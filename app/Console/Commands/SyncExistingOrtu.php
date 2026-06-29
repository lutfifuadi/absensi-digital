<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncExistingOrtu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-existing-ortu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync existing ortu_user_id from siswa table to siswa_ortu pivot table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sync for existing parent-student relationships...');

        $siswaList = Siswa::whereNotNull('ortu_user_id')->get();
        $total = $siswaList->count();

        if ($total === 0) {
            $this->info('No siswa records with ortu_user_id found.');
            return self::SUCCESS;
        }

        $this->info("Found {$total} siswa records to process.");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $synced = 0;
        $skipped = 0;

        foreach ($siswaList as $siswa) {
            // Check if relationship already exists in pivot table to prevent duplicates
            $exists = DB::table('siswa_ortu')
                ->where('siswa_id', $siswa->id)
                ->where('ortu_user_id', $siswa->ortu_user_id)
                ->exists();

            if (!$exists) {
                DB::table('siswa_ortu')->insert([
                    'siswa_id' => $siswa->id,
                    'ortu_user_id' => $siswa->ortu_user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $synced++;
            } else {
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Sync completed successfully!");
        $this->line("Synced: <info>{$synced}</info> relationships.");
        $this->line("Skipped (already exists): <comment>{$skipped}</comment> relationships.");

        return self::SUCCESS;
    }
}

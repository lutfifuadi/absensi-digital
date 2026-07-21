<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class DemoReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset
                            {--fresh : Jalankan migrate:fresh --seed alih-alih restore dari dump}
                            {--generate : Generate SQL dump baru dari kondisi database saat ini}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset database demo ke kondisi pabrikan (data fresh untuk presentasi klien)';

    /**
     * Path penyimpanan SQL dump.
     */
    protected string $dumpPath;

    public function __construct()
    {
        parent::__construct();
        $this->dumpPath = storage_path('demo/demo-fresh.sql');
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Mode: Generate dump
        if ($this->option('generate')) {
            return $this->generateDump();
        }

        // Mode: Restore dari dump (default)
        if (!$this->option('fresh') && File::exists($this->dumpPath)) {
            return $this->restoreFromDump();
        }

        // Mode: Fresh migrate + seed (fallback)
        return $this->freshMigrateAndSeed();
    }

    /**
     * Restore database dari file SQL dump.
     */
    protected function restoreFromDump(): int
    {
        $this->components->task('Menyiapkan restore database demo', function () {});

        if (!File::exists($this->dumpPath)) {
            $this->components->error('File dump tidak ditemukan: ' . $this->dumpPath);
            $this->line('Jalankan <comment>php artisan demo:reset --generate</comment> untuk membuat dump terlebih dahulu.');
            return self::FAILURE;
        }

        // Baca konfigurasi database
        $connection = config('database.default');
        $host = config("database.connections.{$connection}.host");
        $port = config("database.connections.{$connection}.port");
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");

        // Nonaktifkan foreign key checks untuk menghindari error saat restore
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $this->components->task('Merestore database dari dump', function () use ($host, $port, $database, $username, $password) {
            $dumpFile = $this->dumpPath;
            $command = sprintf(
                'mysql -h %s -P %s -u %s %s %s < %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                $password ? '-p' . escapeshellarg($password) : '',
                escapeshellarg($database),
                escapeshellarg($dumpFile)
            );

            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \RuntimeException('Gagal restore database: ' . implode("\n", $output));
            }
        });

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        // Bersihkan cache
        $this->clearCache();

        $this->components->info('✅ Database demo berhasil direset ke kondisi pabrikan!');
        return self::SUCCESS;
    }

    /**
     * Jalankan migrate:fresh --seed untuk reset database.
     */
    protected function freshMigrateAndSeed(): int
    {
        $this->components->task('Menjalankan migrate:fresh --seed', function () {
            $exitCode = Artisan::call('migrate:fresh', [
                '--seed' => true,
                '--force' => true,
            ]);

            if ($exitCode !== 0) {
                throw new \RuntimeException('migrate:fresh gagal dengan kode: ' . $exitCode . "\n" . Artisan::output());
            }
        });

        // Bersihkan cache
        $this->clearCache();

        $this->components->info('✅ Database demo berhasil direset via migrate:fresh --seed!');
        return self::SUCCESS;
    }

    /**
     * Generate SQL dump dari kondisi database saat ini.
     */
    protected function generateDump(): int
    {
        $this->components->task('Menyiapkan generate SQL dump', function () {});

        // Pastikan folder storage/demo ada
        $dumpDir = dirname($this->dumpPath);
        if (!File::isDirectory($dumpDir)) {
            File::makeDirectory($dumpDir, 0755, true);
        }

        // Baca konfigurasi database
        $connection = config('database.default');
        $host = config("database.connections.{$connection}.host");
        $port = config("database.connections.{$connection}.port");
        $database = config("database.connections.{$connection}.database");
        $username = config("database.connections.{$connection}.username");
        $password = config("database.connections.{$connection}.password");

        $this->components->task('Generate SQL dump via mysqldump', function () use ($host, $port, $database, $username, $password) {
            $command = sprintf(
                'mysqldump -h %s -P %s -u %s %s %s --routines --triggers --add-drop-table > %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                $password ? '-p' . escapeshellarg($password) : '',
                escapeshellarg($database),
                escapeshellarg($this->dumpPath)
            );

            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \RuntimeException('Gagal generate dump: ' . implode("\n", $output));
            }
        });

        $size = File::size($this->dumpPath);
        $this->components->info(sprintf(
            '✅ SQL dump berhasil dibuat: %s (%s)',
            $this->dumpPath,
            $this->formatBytes($size)
        ));

        return self::SUCCESS;
    }

    /**
     * Bersihkan berbagai cache Laravel.
     */
    protected function clearCache(): void
    {
        $this->components->task('Membersihkan cache', function () {
            Artisan::call('cache:clear', ['--quiet' => true]);
            Artisan::call('config:clear', ['--quiet' => true]);
            Artisan::call('view:clear', ['--quiet' => true]);
            Artisan::call('route:clear', ['--quiet' => true]);
        });
    }

    /**
     * Format bytes ke human readable.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }
}

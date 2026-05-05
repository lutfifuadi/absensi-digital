<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BuildAssets extends Command
{
    protected $signature = 'app:build';
    protected $description = 'Install npm dependencies dan build frontend assets';

    public function handle(): int
    {
        $this->info('Installing npm dependencies...');
        passthru('npm install', $installCode);

        if ($installCode !== 0) {
            $this->error('npm install gagal.');
            return self::FAILURE;
        }

        $this->info('Building assets...');
        passthru('npm run build', $buildCode);

        if ($buildCode !== 0) {
            $this->error('npm run build gagal.');
            return self::FAILURE;
        }

        $this->info('Assets berhasil di-build!');
        return self::SUCCESS;
    }
}

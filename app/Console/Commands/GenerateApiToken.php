<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:generate-token {email? : Email dari admin untuk mengaitkan token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API Token untuk sinkronisasi dari aplikasi eksternal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        if (!$email) {
            $email = $this->ask('Masukkan email Super Admin untuk dikaitkan dengan token API');
        }

        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            $this->error("User dengan email {$email} tidak ditemukan.");
            return 1;
        }

        if (!$user->isSuperAdmin()) {
            $this->warn("Perhatian: User {$email} bukan super_admin. Anda yakin ingin memberikan akses API sync? (yes/no)");
            if (!$this->confirm('Lanjutkan?')) {
                return 0;
            }
        }

        $tokenName = $this->ask('Masukkan nama untuk token ini (misal: "Aplikasi Pusat Sync")', 'Sync-App');
        
        // Hapus token lama dengan nama yang sama jika ada (opsional, tapi disarankan supaya tidak dangkring)
        $user->tokens()->where('name', $tokenName)->delete();

        $token = $user->createToken($tokenName);

        $this->info("Token API berhasil dibuat untuk user {$user->name}!");
        $this->newLine();
        $this->warn('--- SIMPAN TOKEN INI BAIK-BAIK, HANYA MUNCUL SEKALI ---');
        $this->line($token->plainTextToken);
        $this->warn('-------------------------------------------------------');

        return 0;
    }
}

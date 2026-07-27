<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengaturanAutoAlphaSeeder extends Seeder
{
    /**
     * Seeder untuk menambahkan key-value pengaturan fitur auto-alpha.
     *
     * - auto_alpha_siswa_enabled : Toggle on/off proses auto-alpha untuk siswa
     * - auto_alpha_wa_notif      : Toggle on/off pengiriman WA ke orang tua saat auto-alpha
     *
     * Gunakan perintah berikut untuk menjalankan seeder ini secara standalone:
     *   php artisan db:seed --class=PengaturanAutoAlphaSeeder
     *
     * Seeder ini TIDAK dimasukkan ke DatabaseSeeder.php agar tidak mengganggu data production.
     * Aman dijalankan berulang karena menggunakan updateOrInsert.
     */
    public function run(): void
    {
        $settings = [
            [
                'key'   => 'auto_alpha_siswa_enabled',
                'value' => 'Ya',
                'group' => 'Notifikasi',
            ],
            [
                'key'   => 'auto_alpha_wa_notif',
                'value' => 'Ya',
                'group' => 'Notifikasi',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('pengaturan')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value'      => $setting['value'],
                    'group'      => $setting['group'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // BUG-004: Wrap dengan null check untuk test context
            if ($this->command) {
                $this->command->info("✔ Key '{$setting['key']}' berhasil ditambahkan/diperbarui.");
            }
        }

        // BUG-004: Wrap dengan null check untuk test context
        if ($this->command) {
            $this->command->info('');
            $this->command->info('PengaturanAutoAlphaSeeder selesai. ' . count($settings) . ' key diproses.');
        }
    }
}

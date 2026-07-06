<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengaturanIdCardSeeder extends Seeder
{
    /**
     * Seeder untuk menambahkan key-value pengaturan fitur cetak kartu identitas.
     *
     * Gunakan perintah berikut untuk menjalankan seeder ini secara standalone:
     *   php artisan db:seed --class=PengaturanIdCardSeeder
     *
     * Seeder ini TIDAK dimasukkan ke DatabaseSeeder.php agar tidak mengganggu data production.
     * Aman dijalankan berulang karena menggunakan updateOrCreate.
     */
    public function run(): void
    {
        $settings = [
            [
                'key'   => 'tanda_tangan_kepala_sekolah',
                'value' => '',
                'group' => 'umum',
            ],
            [
                'key'   => 'cap_sekolah',
                'value' => '',
                'group' => 'umum',
            ],
            [
                'key'   => 'kota_penerbitan',
                'value' => '',
                'group' => 'umum',
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

            $this->command->info("✔ Key '{$setting['key']}' berhasil ditambahkan/diperbarui.");
        }

        $this->command->info('');
        $this->command->info('PengaturanIdCardSeeder selesai. ' . count($settings) . ' key diproses.');
    }
}

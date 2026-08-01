<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use App\Support\PengaturanDefaults;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $definitions = PengaturanDefaults::definitions();

        foreach ($definitions as $key => $meta) {
            Pengaturan::firstOrCreate(
                ['key' => $key],
                [
                    'value' => $meta['default'],
                    'group' => $meta['group'],
                ]
            );
        }
    }
}

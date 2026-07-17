<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'kode' => 'UMUM',
                'nama' => 'Umum',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'TKJ',
                'nama' => 'Teknik Komputer & Jaringan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'TBSM',
                'nama' => 'Teknik & Bisnis Sepeda Motor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'TABUS',
                'nama' => 'Tata Busana',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($data as $item) {
            DB::table('jurusan')->updateOrInsert(
                ['kode' => $item['kode']],
                $item
            );
        }
    }
}

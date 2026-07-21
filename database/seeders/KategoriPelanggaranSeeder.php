<?php

namespace Database\Seeders;

use App\Models\KategoriPelanggaran;
use Illuminate\Database\Seeder;

class KategoriPelanggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = [
            [
                'nama' => 'Tata Tertib',
                'deskripsi' => 'Pelanggaran terkait kedisiplinan umum dan aturan sekolah.',
                'warna' => '#ef4444', // Merah
                'urutan' => 1,
                'is_aktif' => true,
            ],
            [
                'nama' => 'Moral',
                'deskripsi' => 'Pelanggaran etika, perilaku, sopan santun, atau integritas.',
                'warna' => '#f97316', // Oranye
                'urutan' => 2,
                'is_aktif' => true,
            ],
            [
                'nama' => 'Akademik',
                'deskripsi' => 'Pelanggaran yang berhubungan dengan aktivitas belajar mengajar.',
                'warna' => '#3b82f6', // Biru
                'urutan' => 3,
                'is_aktif' => true,
            ],
            [
                'nama' => 'Kehadiran',
                'deskripsi' => 'Pelanggaran presensi, bolos, atau keterlambatan.',
                'warna' => '#eab308', // Kuning
                'urutan' => 4,
                'is_aktif' => true,
            ]
        ];

        foreach ($kategori as $item) {
            KategoriPelanggaran::updateOrCreate(
                ['nama' => $item['nama']],
                $item
            );
        }
    }
}

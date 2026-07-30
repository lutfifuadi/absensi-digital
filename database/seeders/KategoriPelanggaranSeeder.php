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
                'nama' => 'Kedisiplinan & Kerapian',
                'deskripsi' => 'Pelanggaran terkait kedisiplinan umum, pakaian, atribut, dan kerapian diri.',
                'warna' => '#ef4444', // Merah
                'urutan' => 1,
                'is_aktif' => true,
            ],
            [
                'nama' => 'Kehadiran & Akses',
                'deskripsi' => 'Pelanggaran presensi, bolos kelas, keterlambatan, atau keluar area sekolah tanpa izin.',
                'warna' => '#eab308', // Kuning
                'urutan' => 2,
                'is_aktif' => true,
            ],
            [
                'nama' => 'Etika & Perilaku',
                'deskripsi' => 'Pelanggaran etika, sikap, sopan santun, bullying, atau pengrusakan fasilitas.',
                'warna' => '#f97316', // Oranye
                'urutan' => 3,
                'is_aktif' => true,
            ],
            [
                'nama' => 'Pelanggaran Berat & Keamanan',
                'deskripsi' => 'Pelanggaran berat terkait perkelahian, senjata, miras, narkoba, atau ancaman keamanan.',
                'warna' => '#b91c1c', // Merah Tua
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

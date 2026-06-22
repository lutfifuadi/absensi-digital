<?php

namespace Database\Seeders;

use App\Models\GuideCategory;
use Illuminate\Database\Seeder;

class GuideCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Panduan Publik',
                'slug' => 'panduan-publik',
                'description' => 'Panduan umum yang dapat diakses oleh semua pengguna aplikasi.',
                'icon' => 'globe',
                'order' => 1,
            ],
            [
                'name' => 'Panduan Siswa',
                'slug' => 'panduan-siswa',
                'description' => 'Panduan khusus untuk siswa dalam menggunakan fitur presensi dan lainnya.',
                'icon' => 'graduation-cap',
                'order' => 2,
            ],
            [
                'name' => 'Panduan Guru',
                'slug' => 'panduan-guru',
                'description' => 'Panduan untuk guru dalam mengelola kelas dan presensi.',
                'icon' => 'chalkboard-teacher',
                'order' => 3,
            ],
            [
                'name' => 'Panduan Wali Kelas',
                'slug' => 'panduan-wali-kelas',
                'description' => 'Panduan untuk wali kelas dalam memantau kehadiran dan perkembangan siswa.',
                'icon' => 'users',
                'order' => 4,
            ],
            [
                'name' => 'Panduan Orang Tua',
                'slug' => 'panduan-orang-tua',
                'description' => 'Panduan untuk orang tua dalam memantau presensi dan aktivitas anak.',
                'icon' => 'family',
                'order' => 5,
            ],
            [
                'name' => 'Panduan Staff TU',
                'slug' => 'panduan-staff-tu',
                'description' => 'Panduan untuk staff tata usaha dalam mengelola data administrasi.',
                'icon' => 'clipboard',
                'order' => 6,
            ],
            [
                'name' => 'Panduan Operator',
                'slug' => 'panduan-operator',
                'description' => 'Panduan untuk operator dalam menjalankan sistem presensi sehari-hari.',
                'icon' => 'cogs',
                'order' => 7,
            ],
            [
                'name' => 'Panduan Admin Sekolah',
                'slug' => 'panduan-admin-sekolah',
                'description' => 'Panduan untuk admin sekolah dalam mengelola pengaturan dan konfigurasi aplikasi.',
                'icon' => 'shield',
                'order' => 8,
            ],
            [
                'name' => 'Panduan Super Admin',
                'slug' => 'panduan-super-admin',
                'description' => 'Panduan untuk super admin dalam mengelola seluruh aspek aplikasi di tingkat tertinggi.',
                'icon' => 'crown',
                'order' => 9,
            ],
            [
                'name' => 'FAQ & Troubleshooting',
                'slug' => 'faq-troubleshooting',
                'description' => 'Kumpulan pertanyaan umum dan panduan pemecahan masalah.',
                'icon' => 'question-circle',
                'order' => 10,
            ],
            [
                'name' => 'Fitur Teknis & Integrasi',
                'slug' => 'fitur-teknis-integrasi',
                'description' => 'Panduan tentang fitur teknis dan integrasi dengan layanan eksternal.',
                'icon' => 'plug',
                'order' => 11,
            ],
        ];

        foreach ($categories as $category) {
            GuideCategory::create($category);
        }

        $this->command->info('Berhasil menambahkan ' . count($categories) . ' kategori panduan.');
    }
}

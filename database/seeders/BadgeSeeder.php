<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Burung Pagi (Early Bird)',
                'icon' => 'tabler-sun',
                'description' => 'Presensi terawal sebelum pukul 06.45 WIB selama 5 hari berturut-turut.',
                'badge_type' => 'individual',
                'requirement_days' => 5,
                'requirement_type' => 'consecutive',
                'is_active' => true,
            ],
            [
                'name' => 'Streak Master (7 Hari)',
                'icon' => 'tabler-flame',
                'description' => 'Hadir sekolah secara konsisten tanpa terputus selama 7 hari berturut-turut.',
                'badge_type' => 'individual',
                'requirement_days' => 7,
                'requirement_type' => 'consecutive',
                'is_active' => true,
            ],
            [
                'name' => 'Bebas Jam Karet',
                'icon' => 'tabler-clock-check',
                'description' => 'Tidak pernah terlambat presensi sama sekali selama 1 bulan penuh.',
                'badge_type' => 'individual',
                'requirement_days' => 20,
                'requirement_type' => 'consecutive',
                'is_active' => true,
            ],
            [
                'name' => 'Kehadiran Sempurna',
                'icon' => 'tabler-diamond',
                'description' => 'Tingkat kehadiran 100% tanpa alpha, sakit, atau izin selama 1 semester.',
                'badge_type' => 'individual',
                'requirement_days' => 100,
                'requirement_type' => 'consecutive',
                'is_active' => true,
            ],
            [
                'name' => 'Century Club',
                'icon' => 'tabler-certificate',
                'description' => 'Telah mengumpulkan total akumulasi 100 hari presensi hadir.',
                'badge_type' => 'individual',
                'requirement_days' => 100,
                'requirement_type' => 'total',
                'is_active' => true,
            ],
            [
                'name' => 'Clean Sheet',
                'icon' => 'tabler-shield-check',
                'description' => 'Bebas dari segala bentuk poin pelanggaran dan teguran selama 1 bulan.',
                'badge_type' => 'individual',
                'requirement_days' => 30,
                'requirement_type' => 'consecutive',
                'is_active' => true,
            ],
            [
                'name' => 'Rising Star',
                'icon' => 'tabler-trending-up',
                'description' => 'Menunjukkan peningkatan kedisiplinan dan persentase kehadiran secara signifikan.',
                'badge_type' => 'individual',
                'requirement_days' => 15,
                'requirement_type' => 'total',
                'is_active' => true,
            ],
            [
                'name' => 'Solid Squad (Kelas Compact)',
                'icon' => 'tabler-users-group',
                'description' => 'Seluruh siswa dalam 1 kelas hadir 100% tanpa ada yang terlambat/absen.',
                'badge_type' => 'class',
                'requirement_days' => 1,
                'requirement_type' => 'consecutive',
                'is_active' => true,
            ],
            [
                'name' => 'Zero Alpha Class',
                'icon' => 'tabler-shield-star',
                'description' => 'Kelas teladan yang berhasil mempertahankan 0% catatan Alpha selama 1 bulan.',
                'badge_type' => 'class',
                'requirement_days' => 30,
                'requirement_type' => 'consecutive',
                'is_active' => true,
            ],
            [
                'name' => 'Kelas Juara',
                'icon' => 'tabler-trophy',
                'description' => 'Meraih peringkat #1 Papan Peringkat Kelas pada rekapitulasi semester.',
                'badge_type' => 'class',
                'requirement_days' => 1,
                'requirement_type' => 'total',
                'is_active' => true,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::firstOrCreate(
                ['name' => $badge['name']],
                $badge
            );
        }
    }
}

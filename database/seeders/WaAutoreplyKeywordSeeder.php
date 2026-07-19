<?php

namespace Database\Seeders;

use App\Models\WaAutoreplyKeyword;
use Illuminate\Database\Seeder;

class WaAutoreplyKeywordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $keywords = [
            [
                'keyword' => '#absen',
                'match_type' => 'exact',
                'is_validation_required' => true,
                'is_active' => true,
                'notification_template_type' => 'autoreply_absen',
            ],
            [
                'keyword' => '#rekap',
                'match_type' => 'exact',
                'is_validation_required' => true,
                'is_active' => true,
                'notification_template_type' => 'autoreply_rekap',
            ],
            [
                'keyword' => '#link',
                'match_type' => 'exact',
                'is_validation_required' => false,
                'is_active' => true,
                'notification_template_type' => 'autoreply_link',
            ],
            [
                'keyword' => '#bantuan',
                'match_type' => 'exact',
                'is_validation_required' => false,
                'is_active' => true,
                'notification_template_type' => 'autoreply_bantuan',
            ],
            [
                'keyword' => '#pengaduan',
                'match_type' => 'exact',
                'is_validation_required' => false,
                'is_active' => true,
                'notification_template_type' => 'autoreply_pengaduan',
            ],
        ];

        foreach ($keywords as $kw) {
            WaAutoreplyKeyword::updateOrCreate(
                ['keyword' => $kw['keyword']],
                [
                    'match_type' => $kw['match_type'],
                    'is_validation_required' => $kw['is_validation_required'],
                    'is_active' => $kw['is_active'],
                    'notification_template_type' => $kw['notification_template_type'],
                ]
            );
        }

        echo "WA Autoreply Keywords seeded successfully!" . PHP_EOL;
    }
}

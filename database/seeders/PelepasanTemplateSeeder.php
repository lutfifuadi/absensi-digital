<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IdCardTemplate;

class PelepasanTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $config = [
            'canvas' => [
                'width' => 500,
                'height' => 320
            ],
            'elements' => [
                'photo' => [
                    'x' => 30,
                    'y' => 80,
                    'w' => 100,
                    'h' => 120,
                    'show' => true
                ],
                'name' => [
                    'x' => 150,
                    'y' => 100,
                    'size' => 16,
                    'color' => '#facc15', // Gold
                    'align' => 'left',
                    'show' => true
                ],
                'id_number' => [
                    'x' => 150,
                    'y' => 135,
                    'size' => 11,
                    'color' => '#ffffff',
                    'align' => 'left',
                    'show' => true
                ],
                'class' => [
                    'x' => 150,
                    'y' => 195,
                    'size' => 12,
                    'color' => '#ffffff',
                    'align' => 'left',
                    'show' => true
                ],
                'qr' => [
                    'x' => 360,
                    'y' => 170,
                    'w' => 110,
                    'h' => 110,
                    'show' => true
                ]
            ]
        ];

        IdCardTemplate::updateOrCreate(
            ['type' => 'pelepasan'],
            [
                'name' => 'Template Pelepasan Elegan (Navy Gold)',
                'config' => $config,
                'is_active' => true,
                'background_path' => null // Background bisa diupload manual oleh user agar lebih pas
            ]
        );
    }
}

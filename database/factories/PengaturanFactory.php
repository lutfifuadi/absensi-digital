<?php

namespace Database\Factories;

use App\Models\Pengaturan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pengaturan>
 */
class PengaturanFactory extends Factory
{
    protected $model = Pengaturan::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->word(),
            'value' => fake()->sentence(),
            'group' => fake()->randomElement(['umum', 'sekolah', 'ai', 'aplikasi']),
        ];
    }
}

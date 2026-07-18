<?php

namespace Database\Factories;

use App\Models\GuideCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GuideCategory>
 */
class GuideCategoryFactory extends Factory
{
    protected $model = GuideCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(2),
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement(['book', 'settings', 'help-circle', 'info', 'star']),
            'order' => fake()->numberBetween(0, 50),
        ];
    }
}

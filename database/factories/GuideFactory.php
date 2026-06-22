<?php

namespace Database\Factories;

use App\Models\Guide;
use App\Models\GuideCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guide>
 */
class GuideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(rand(4, 8));

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => fake()->paragraphs(rand(5, 15), true),
            'excerpt' => fake()->paragraph(2),
            'category_id' => GuideCategory::factory(),
            'role_target' => fake()->randomElement([
                null,
                'public',
                'siswa',
                'guru',
                'admin_sekolah',
                'super_admin',
                'siswa,guru',
                'guru,admin_sekolah',
            ]),
            'featured_image' => fake()->optional(0.3)->imageUrl(800, 400, 'technology', true),
            'author_id' => User::factory(),
            'status' => fake()->randomElement(['draft', 'published', 'published', 'published', 'archived']),
            'order' => fake()->optional(0.7)->numberBetween(0, 100),
            'is_featured' => fake()->boolean(20),
            'metadata' => fake()->optional(0.4)->passthrough([
                'reading_time' => fake()->numberBetween(2, 15) . ' menit',
                'difficulty' => fake()->randomElement(['pemula', 'menengah', 'lanjutan']),
                'tags' => fake()->words(3),
            ]),
            'published_at' => fake()->optional(0.7)->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Indikator bahwa panduan sudah dipublikasikan.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Indikator bahwa panduan masih draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indikator bahwa panduan diarsipkan.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    /**
     * Indikator bahwa panduan difeaturekan.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Menentukan target role tertentu.
     */
    public function forRole(string $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role_target' => $role,
        ]);
    }
}

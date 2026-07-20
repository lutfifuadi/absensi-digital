<?php

namespace Database\Factories;

use App\Models\LeaveLimit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeaveLimit>
 */
class LeaveLimitFactory extends Factory
{
    protected $model = LeaveLimit::class;

    public function definition(): array
    {
        return [
            'name'          => fake()->sentence(3),
            'leave_type'    => fake()->randomElement(['sick', 'permission', 'all']),
            'max_days'      => fake()->numberBetween(1, 30),
            'period'        => fake()->randomElement(['monthly', 'semester', 'yearly']),
            'action_type'   => fake()->randomElement(['warning', 'block']),
            'target_roles'  => [fake()->randomElement(['siswa', 'guru', 'staff_tu'])],
            'target_grades' => null,
            'is_active'     => true,
        ];
    }

    /**
     * Indicate that the limit is for a specific role.
     */
    public function forRole(string $role): static
    {
        return $this->state(fn (array $attributes) => [
            'target_roles' => [$role],
        ]);
    }

    /**
     * Indicate that the limit blocks when exceeded.
     */
    public function blocking(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => 'block',
        ]);
    }

    /**
     * Indicate that the limit warns when exceeded.
     */
    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => 'warning',
        ]);
    }

    /**
     * Mark the limit as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}

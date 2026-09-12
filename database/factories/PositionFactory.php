<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->jobTitle(),
            'description' => fake()->optional()->sentence(),
            'can_teach' => false,
            'is_active' => true,
        ];
    }

    public function canTeach(): static
    {
        return $this->state(fn (array $attributes): array => [
            'can_teach' => true,
        ]);
    }
}

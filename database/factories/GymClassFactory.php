<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\GymClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GymClass>
 */
class GymClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'type' => 'general',
            'maximum_capacity' => 20,
            'requires_premium' => false,
            'is_active' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Benefit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Benefit>
 */
class BenefitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'benefit_type' => fake()->word(),
            'value' => fake()->randomFloat(2, 0, 100),
            'usage_limit' => fake()->numberBetween(1, 10),
            'usage_period' => fake()->word(),
            'is_active' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Gym',
            'code' => 'SUC-'.fake()->unique()->numerify('###'),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'is_active' => true,
            'opening_time' => '06:00:00',
            'closing_time' => '21:00:00',
        ];
    }
}

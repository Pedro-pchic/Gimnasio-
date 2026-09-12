<?php

namespace Database\Factories;

use App\DiscountType;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Discount>
 */
class DiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'type' => DiscountType::Percentage->value,
            'value' => fake()->randomFloat(2, 1, 50),
            'start_date' => null,
            'end_date' => null,
            'is_active' => true,
        ];
    }
}

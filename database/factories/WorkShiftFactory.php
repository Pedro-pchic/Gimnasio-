<?php

namespace Database\Factories;

use App\Models\WorkShift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkShift>
 */
class WorkShiftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\GymClass;
use App\Models\GymClassSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GymClassSchedule>
 */
class GymClassScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gym_class_id' => GymClass::factory(),
            'day_of_week' => 'monday',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'is_active' => true,
        ];
    }
}

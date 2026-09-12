<?php

namespace Database\Factories;

use App\GymClassEnrollmentStatus;
use App\Models\Client;
use App\Models\GymClassEnrollment;
use App\Models\GymClassSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GymClassEnrollment>
 */
class GymClassEnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'gym_class_schedule_id' => GymClassSchedule::factory(),
            'enrollment_date' => now()->toDateString(),
            'status' => GymClassEnrollmentStatus::Enrolled,
        ];
    }
}

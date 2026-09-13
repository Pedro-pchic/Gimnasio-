<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\WorkShift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeShiftAssignment>
 */
class EmployeeShiftAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'work_shift_id' => WorkShift::factory(),
            'effective_from' => '2026-09-01',
            'effective_until' => null,
            'day_of_week' => null,
            'is_active' => true,
        ];
    }
}

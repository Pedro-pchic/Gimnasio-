<?php

namespace Database\Factories;

use App\EmployeeAttendanceMethod;
use App\EmployeeAttendanceStatus;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeAttendance>
 */
class EmployeeAttendanceFactory extends Factory
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
            'branch_id' => Branch::factory(),
            'work_shift_id' => null,
            'attendance_date' => now()->toDateString(),
            'entry_time' => '08:00:00',
            'exit_time' => null,
            'registration_method' => EmployeeAttendanceMethod::Manual,
            'status' => EmployeeAttendanceStatus::Present,
            'observations' => null,
        ];
    }
}

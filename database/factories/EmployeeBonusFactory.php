<?php

namespace Database\Factories;

use App\EmployeeBonusStatus;
use App\Models\Employee;
use App\Models\EmployeeBonus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeBonus>
 */
class EmployeeBonusFactory extends Factory
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
            'period' => now()->format('Y-m'),
            'amount' => 100,
            'reason' => fake()->sentence(),
            'status' => EmployeeBonusStatus::Pending->value,
            'approved_by' => null,
        ];
    }
}

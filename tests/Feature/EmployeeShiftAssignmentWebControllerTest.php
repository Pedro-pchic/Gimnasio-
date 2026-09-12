<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkShift;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class EmployeeShiftAssignmentWebControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_manager_assigns_a_shift_to_an_employee(): void
    {
        $manager = $this->userWithRole('Gerente de sucursal');
        $employee = Employee::factory()->create();
        $workShift = WorkShift::factory()->create();

        $response = $this->actingAs($manager)->post('/employee-shift-assignments', [
            'employee_id' => $employee->id,
            'work_shift_id' => $workShift->id,
            'effective_from' => '2026-09-01',
            'effective_until' => null,
            'day_of_week' => 'monday',
            'is_active' => true,
        ]);
        $assignment = EmployeeShiftAssignment::query()->firstOrFail();

        $response->assertRedirectToRoute('employees.show', $employee);
        $this->assertDatabaseHas('employee_shift_assignments', [
            'id' => $assignment->id,
            'employee_id' => $employee->id,
            'work_shift_id' => $workShift->id,
            'day_of_week' => 'monday',
            'is_active' => true,
        ]);
    }

    public function test_manager_cannot_create_an_overlapping_incompatible_assignment(): void
    {
        $manager = $this->userWithRole('Gerente de sucursal');
        $employee = Employee::factory()->create();
        EmployeeShiftAssignment::factory()->for($employee)->create([
            'effective_from' => '2026-09-01',
            'effective_until' => null,
            'day_of_week' => null,
        ]);
        $workShift = WorkShift::factory()->create();

        $this->actingAs($manager)
            ->post('/employee-shift-assignments', [
                'employee_id' => $employee->id,
                'work_shift_id' => $workShift->id,
                'effective_from' => '2026-09-10',
                'effective_until' => null,
                'day_of_week' => 'monday',
                'is_active' => true,
            ])
            ->assertInvalid(['employee_id']);

        $this->assertDatabaseCount('employee_shift_assignments', 1);
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user->fresh();
    }
}

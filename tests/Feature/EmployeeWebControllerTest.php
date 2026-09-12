<?php

namespace Tests\Feature;

use App\EmployeeStatus;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class EmployeeWebControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_manager_creates_employee_for_a_branch_with_an_optional_user_account(): void
    {
        $manager = $this->userWithRole('Gerente de sucursal');
        $branch = Branch::factory()->create();
        $position = Position::factory()->create();
        $account = User::factory()->create();

        $response = $this->actingAs($manager)->post('/employees', [
            'branch_id' => $branch->id,
            'position_id' => $position->id,
            'user_id' => $account->id,
            'code' => 'EMP-100001',
            'first_name' => 'Maria',
            'last_name' => 'Lopez',
            'phone' => '5555-1111',
            'email' => 'maria.lopez@example.com',
            'hired_at' => '2026-09-01',
            'status' => EmployeeStatus::Active->value,
        ]);
        $employee = Employee::query()->where('code', 'EMP-100001')->firstOrFail();

        $response->assertRedirectToRoute('employees.show', $employee);
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'branch_id' => $branch->id,
            'position_id' => $position->id,
            'user_id' => $account->id,
            'status' => EmployeeStatus::Active->value,
        ]);
        $this->assertSame($account->id, $employee->user->id);
    }

    public function test_employee_creation_rejects_a_duplicate_internal_code(): void
    {
        $manager = $this->userWithRole('Gerente de sucursal');
        $employee = Employee::factory()->create(['code' => 'EMP-100002']);

        $this->actingAs($manager)
            ->post('/employees', [
                'branch_id' => $employee->branch_id,
                'position_id' => $employee->position_id,
                'code' => 'EMP-100002',
                'first_name' => 'Codigo',
                'last_name' => 'Duplicado',
                'hired_at' => '2026-09-01',
                'status' => EmployeeStatus::Active->value,
            ])
            ->assertInvalid(['code']);

        $this->assertDatabaseCount('employees', 1);
    }

    public function test_receptionist_cannot_manage_employees(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $employee = Employee::factory()->create();

        $this->actingAs($receptionist)
            ->get("/employees/{$employee->id}/edit")
            ->assertForbidden();
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user->fresh();
    }
}

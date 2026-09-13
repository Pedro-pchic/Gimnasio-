<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PositionWebControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_manager_creates_a_teaching_position(): void
    {
        $manager = $this->userWithRole('Gerente de sucursal');

        $this->actingAs($manager)
            ->post('/positions', [
                'name' => 'Entrenador funcional',
                'description' => 'Imparte sesiones funcionales.',
                'can_teach' => true,
                'is_active' => true,
            ])
            ->assertRedirectToRoute('positions.index');

        $this->assertDatabaseHas('positions', [
            'name' => 'Entrenador funcional',
            'can_teach' => true,
            'is_active' => true,
        ]);
    }

    public function test_deactivating_a_position_keeps_its_employee_history(): void
    {
        $manager = $this->userWithRole('Gerente de sucursal');
        $position = Position::factory()->create();
        $employee = Employee::factory()->for($position)->create();

        $this->actingAs($manager)
            ->patch("/positions/{$position->id}/status")
            ->assertRedirect();

        $this->assertDatabaseHas('positions', ['id' => $position->id, 'is_active' => false]);
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'position_id' => $position->id]);
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user->fresh();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\GymClass;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GymClassWebControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_manager_creates_an_active_general_class(): void
    {
        $user = $this->userWithRole('Gerente de sucursal');
        $branch = Branch::factory()->create();

        $response = $this->actingAs($user)->post('/gym-classes', [
            'branch_id' => $branch->id,
            'name' => 'Funcional matutino',
            'description' => 'Entrenamiento de fuerza y movilidad.',
            'type' => 'general',
            'maximum_capacity' => 20,
            'is_active' => true,
        ]);
        $gymClass = GymClass::query()->where('name', 'Funcional matutino')->firstOrFail();

        $response->assertRedirectToRoute('gym-classes.show', $gymClass);
        $this->assertSame(20, $gymClass->maximum_capacity);
        $this->assertTrue($gymClass->is_active);
    }

    public function test_class_creation_rejects_a_capacity_below_one(): void
    {
        $user = $this->userWithRole('Gerente de sucursal');
        $branch = Branch::factory()->create();

        $this->actingAs($user)
            ->post('/gym-classes', [
                'branch_id' => $branch->id,
                'name' => 'Clase inválida',
                'type' => 'general',
                'maximum_capacity' => 0,
            ])
            ->assertInvalid(['maximum_capacity']);

        $this->assertDatabaseMissing('gym_classes', ['name' => 'Clase inválida']);
    }

    public function test_swimming_and_boxing_require_their_branch_service_when_it_exists(): void
    {
        $user = $this->userWithRole('Gerente de sucursal');
        $branch = Branch::factory()->create();
        $swimmingService = Service::factory()->create(['name' => 'Natación']);
        $boxingService = Service::factory()->create(['name' => 'Boxeo']);

        $this->actingAs($user)
            ->post('/gym-classes', [
                'branch_id' => $branch->id,
                'name' => 'Natación inicial',
                'type' => 'natacion',
                'maximum_capacity' => 10,
            ])
            ->assertInvalid(['branch_id']);

        $branch->services()->attach([$swimmingService->id, $boxingService->id]);

        $this->actingAs($user)
            ->post('/gym-classes', [
                'branch_id' => $branch->id,
                'name' => 'Natación inicial',
                'type' => 'natacion',
                'maximum_capacity' => 10,
            ])
            ->assertRedirect();
        $this->actingAs($user)
            ->post('/gym-classes', [
                'branch_id' => $branch->id,
                'name' => 'Boxeo técnico',
                'type' => 'boxeo',
                'maximum_capacity' => 15,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('gym_classes', ['name' => 'Natación inicial', 'maximum_capacity' => 10]);
        $this->assertDatabaseHas('gym_classes', ['name' => 'Boxeo técnico', 'maximum_capacity' => 15]);
    }

    public function test_receptionist_is_forbidden_from_managing_classes(): void
    {
        $user = $this->userWithRole('Recepcionista');
        $branch = Branch::factory()->create();

        $this->actingAs($user)
            ->post('/gym-classes', [
                'branch_id' => $branch->id,
                'name' => 'Clase restringida',
                'type' => 'general',
                'maximum_capacity' => 10,
            ])
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

<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BranchWebControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authorized_user_creates_branch_and_associates_services(): void
    {
        $user = $this->userWithRole('Administrador general');
        $service = Service::factory()->create();

        $response = $this->actingAs($user)->post('/branches', [
            'name' => 'Sucursal Central',
            'code' => 'CENTRAL',
            'address' => 'Zona 1',
            'phone' => '5555-0101',
            'opening_time' => '06:00',
            'closing_time' => '21:00',
            'service_ids' => [$service->id],
            'is_active' => '1',
        ]);
        $branch = Branch::query()->where('code', 'CENTRAL')->firstOrFail();

        $response->assertRedirectToRoute('branches.show', $branch);
        $this->assertDatabaseHas('branches', ['id' => $branch->id, 'name' => 'Sucursal Central']);
        $this->assertDatabaseHas('branch_service', ['branch_id' => $branch->id, 'service_id' => $service->id]);
    }

    public function test_authorized_user_updates_and_deactivates_branch(): void
    {
        $user = $this->userWithRole('Administrador general');
        $branch = Branch::factory()->create(['is_active' => true]);

        $this->actingAs($user)
            ->put("/branches/{$branch->id}", ['name' => 'Sucursal Actualizada'])
            ->assertRedirectToRoute('branches.show', $branch);
        $this->assertDatabaseHas('branches', ['id' => $branch->id, 'name' => 'Sucursal Actualizada']);

        $this->actingAs($user)
            ->delete("/branches/{$branch->id}")
            ->assertRedirectToRoute('branches.index');
        $this->assertDatabaseHas('branches', ['id' => $branch->id, 'is_active' => 0]);
    }

    public function test_branch_creation_shows_required_field_errors(): void
    {
        $user = $this->userWithRole('Administrador general');

        $this->actingAs($user)
            ->post('/branches', [])
            ->assertInvalid([
                'name' => 'The name field is required.',
                'code' => 'The code field is required.',
            ]);
    }

    public function test_receptionist_cannot_view_branches(): void
    {
        $user = $this->userWithRole('Recepcionista');

        $this->actingAs($user)
            ->get('/branches')
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

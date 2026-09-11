<?php

namespace Tests\Feature;

use App\ClientMembershipStatus;
use App\Models\Branch;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\MembershipType;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_renders_counts_from_existing_records(): void
    {
        $user = $this->administrativeUser();
        $branch = Branch::factory()->create();
        $client = Client::factory()->for($branch)->create();
        $membershipType = MembershipType::factory()->create();
        ClientMembership::factory()->for($client)->for($membershipType)->create([
            'status' => ClientMembershipStatus::Active,
        ]);
        Service::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSee('Sucursales activas')
            ->assertSee('Clientes activos')
            ->assertSee('Tipos de membresía')
            ->assertSee('Membresías activas')
            ->assertSee('Servicios activos');
    }

    private function administrativeUser(): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', 'Administrador general')->valueOrFail('id'));

        return $user->fresh();
    }
}

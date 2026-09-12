<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PermissionGateTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_initial_roles_receive_their_expected_permissions(): void
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);

        foreach ($this->rolePermissions() as $roleName => $permissions) {
            $user = User::factory()->create();
            $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));
            $user = $user->fresh();

            foreach ($permissions['allowed'] as $permission) {
                $this->assertTrue($user->can($permission), "{$roleName} should be able to {$permission}.");
            }

            foreach ($permissions['denied'] as $permission) {
                $this->assertFalse($user->can($permission), "{$roleName} should not be able to {$permission}.");
            }
        }
    }

    /**
     * @return array<string, array{allowed: array<int, string>, denied: array<int, string>}>
     */
    private function rolePermissions(): array
    {
        return [
            'Administrador general' => [
                'allowed' => ['dashboard.view', 'branches.manage', 'benefits.manage', 'client-memberships.manage', 'payments.manage', 'sales.manage', 'renewals.manage'],
                'denied' => [],
            ],
            'Gerente de sucursal' => [
                'allowed' => ['branches.view', 'services.manage', 'clients.manage', 'membership-types.manage', 'benefits.view', 'payments.manage', 'sales.manage', 'renewals.manage'],
                'denied' => ['branches.manage', 'benefits.manage'],
            ],
            'Recepcionista' => [
                'allowed' => ['clients.manage', 'membership-types.view', 'client-memberships.manage', 'payments.manage', 'sales.manage', 'renewals.manage'],
                'denied' => ['branches.view', 'services.manage', 'benefits.view'],
            ],
            'Supervisor' => [
                'allowed' => ['branches.view', 'services.view', 'clients.view', 'client-memberships.view', 'payments.view', 'sales.view', 'renewals.view'],
                'denied' => ['clients.manage', 'membership-types.manage', 'payments.manage', 'sales.manage', 'renewals.manage'],
            ],
            'Instructor / Coach' => [
                'allowed' => ['dashboard.view', 'services.view', 'clients.view'],
                'denied' => ['clients.manage', 'branches.view', 'client-memberships.view', 'payments.view', 'sales.view', 'renewals.view'],
            ],
        ];
    }
}

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
                'allowed' => ['dashboard.view', 'branches.manage', 'benefits.manage', 'referrals.manage', 'referrals.register', 'referrals.apply-credit', 'view_inventory', 'manage_inventory', 'register_inventory_movements', 'view_maintenance', 'manage_maintenance', 'client-memberships.manage', 'payments.manage', 'sales.manage', 'renewals.manage', 'classes.manage', 'schedules.manage', 'enrollments.register', 'enrollments.attendance', 'employees.view', 'employees.manage', 'positions.manage', 'work-shifts.manage', 'employee-attendances.view', 'employee-attendances.register'],
                'denied' => [],
            ],
            'Gerente de sucursal' => [
                'allowed' => ['branches.view', 'services.manage', 'clients.manage', 'referrals.manage', 'referrals.register', 'referrals.apply-credit', 'view_inventory', 'manage_inventory', 'register_inventory_movements', 'view_maintenance', 'manage_maintenance', 'membership-types.manage', 'benefits.view', 'payments.manage', 'sales.manage', 'renewals.manage', 'classes.manage', 'schedules.manage', 'enrollments.register', 'enrollments.attendance', 'employees.view', 'employees.manage', 'positions.manage', 'work-shifts.manage', 'employee-attendances.view', 'employee-attendances.register'],
                'denied' => ['branches.manage', 'benefits.manage'],
            ],
            'Recepcionista' => [
                'allowed' => ['clients.manage', 'referrals.view', 'referrals.register', 'referrals.apply-credit', 'view_inventory', 'membership-types.view', 'client-memberships.manage', 'payments.manage', 'sales.manage', 'renewals.manage', 'classes.view', 'schedules.view', 'enrollments.view', 'enrollments.register', 'employees.view', 'employee-attendances.view', 'employee-attendances.register'],
                'denied' => ['branches.view', 'services.manage', 'benefits.view', 'referrals.manage', 'manage_inventory', 'register_inventory_movements', 'view_maintenance', 'manage_maintenance', 'classes.manage', 'schedules.manage', 'enrollments.attendance', 'employees.manage', 'positions.manage', 'work-shifts.manage'],
            ],
            'Supervisor' => [
                'allowed' => ['branches.view', 'services.view', 'clients.view', 'referrals.view', 'view_inventory', 'register_inventory_movements', 'view_maintenance', 'client-memberships.view', 'payments.view', 'sales.view', 'renewals.view', 'classes.view', 'schedules.view', 'enrollments.view', 'enrollments.attendance', 'employees.view', 'employee-attendances.view', 'employee-attendances.register'],
                'denied' => ['clients.manage', 'referrals.manage', 'referrals.register', 'referrals.apply-credit', 'manage_inventory', 'manage_maintenance', 'membership-types.manage', 'payments.manage', 'sales.manage', 'renewals.manage', 'classes.manage', 'schedules.manage', 'enrollments.register', 'employees.manage', 'positions.manage', 'work-shifts.manage'],
            ],
            'Instructor / Coach' => [
                'allowed' => ['dashboard.view', 'services.view', 'clients.view', 'view_inventory', 'view_maintenance', 'classes.view', 'schedules.view', 'enrollments.view', 'enrollments.attendance'],
                'denied' => ['clients.manage', 'branches.view', 'referrals.view', 'referrals.register', 'referrals.apply-credit', 'manage_inventory', 'register_inventory_movements', 'manage_maintenance', 'client-memberships.view', 'payments.view', 'sales.view', 'renewals.view', 'classes.manage', 'schedules.manage', 'enrollments.register'],
            ],
        ];
    }
}

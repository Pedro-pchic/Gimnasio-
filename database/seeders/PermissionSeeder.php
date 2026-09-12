<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'employees.view' => 'View employees.',
            'employees.manage' => 'Manage employees.',
            'positions.manage' => 'Manage job positions.',
            'work-shifts.manage' => 'Manage work shifts and assignments.',
            'employee-attendances.view' => 'View employee attendance.',
            'employee-attendances.register' => 'Register employee check-ins and check-outs.',
            'classes.view' => 'View classes and activities.',
            'classes.manage' => 'Manage classes and activities.',
            'schedules.view' => 'View class schedules.',
            'schedules.manage' => 'Manage class schedules.',
            'enrollments.view' => 'View class participants.',
            'enrollments.register' => 'Register and cancel class participants.',
            'enrollments.attendance' => 'Mark class participant attendance.',
            'payments.view' => 'View payments.',
            'payments.manage' => 'Register and cancel payments.',
            'sales.view' => 'View sales and receipts.',
            'sales.manage' => 'Register and cancel sales.',
            'renewals.view' => 'View renewals.',
            'renewals.manage' => 'Register renewals with payment.',
            'dashboard.view' => 'View the dashboard.',
            'branches.view' => 'View branches.',
            'branches.manage' => 'Manage branches and their services.',
            'services.view' => 'View services.',
            'services.manage' => 'Manage services.',
            'clients.view' => 'View clients.',
            'clients.manage' => 'Manage clients.',
            'membership-types.view' => 'View membership types.',
            'membership-types.manage' => 'Manage membership types.',
            'benefits.view' => 'View benefits.',
            'benefits.manage' => 'Manage benefits.',
            'client-memberships.view' => 'View client memberships.',
            'client-memberships.manage' => 'Assign, edit, and cancel client memberships.',
            'commercial-partners.view' => 'View commercial partners.',
            'commercial-partners.manage' => 'Manage commercial partners.',
            'third-party-items.view' => 'View third-party products and services.',
            'third-party-items.manage' => 'Manage third-party products and services.',
            'discounts.view' => 'View commercial discounts.',
            'discounts.manage' => 'Manage commercial discounts.',
            'third-party-sales.manage' => 'Register sales of third-party products and services.',
        ];

        foreach ($permissions as $name => $description) {
            Permission::query()->updateOrCreate(
                ['name' => $name],
                ['description' => $description],
            );
        }

        $allPermissionIds = Permission::query()->pluck('id')->all();

        $rolePermissions = [
            'Administrador general' => $allPermissionIds,
            'Gerente de sucursal' => [
                'dashboard.view',
                'branches.view',
                'services.view',
                'services.manage',
                'clients.view',
                'clients.manage',
                'membership-types.view',
                'membership-types.manage',
                'benefits.view',
                'commercial-partners.view',
                'commercial-partners.manage',
                'third-party-items.view',
                'third-party-items.manage',
                'discounts.view',
                'discounts.manage',
                'third-party-sales.manage',
                'client-memberships.view',
                'client-memberships.manage',
                'payments.view',
                'payments.manage',
                'sales.view',
                'sales.manage',
                'renewals.view',
                'renewals.manage',
                'employees.view',
                'employees.manage',
                'positions.manage',
                'work-shifts.manage',
                'employee-attendances.view',
                'employee-attendances.register',
                'classes.view',
                'classes.manage',
                'schedules.view',
                'schedules.manage',
                'enrollments.view',
                'enrollments.register',
                'enrollments.attendance',
            ],
            'Recepcionista' => [
                'dashboard.view',
                'clients.view',
                'clients.manage',
                'membership-types.view',
                'commercial-partners.view',
                'third-party-items.view',
                'discounts.view',
                'third-party-sales.manage',
                'client-memberships.view',
                'client-memberships.manage',
                'payments.view',
                'payments.manage',
                'sales.view',
                'sales.manage',
                'renewals.view',
                'renewals.manage',
                'employees.view',
                'employee-attendances.view',
                'employee-attendances.register',
                'classes.view',
                'schedules.view',
                'enrollments.view',
                'enrollments.register',
            ],
            'Supervisor' => [
                'dashboard.view',
                'branches.view',
                'services.view',
                'clients.view',
                'membership-types.view',
                'benefits.view',
                'commercial-partners.view',
                'third-party-items.view',
                'discounts.view',
                'client-memberships.view',
                'payments.view',
                'sales.view',
                'renewals.view',
                'employees.view',
                'employee-attendances.view',
                'employee-attendances.register',
                'classes.view',
                'schedules.view',
                'enrollments.view',
                'enrollments.attendance',
            ],
            'Instructor / Coach' => [
                'dashboard.view',
                'services.view',
                'clients.view',
                'classes.view',
                'schedules.view',
                'enrollments.view',
                'enrollments.attendance',
            ],
        ];

        foreach ($rolePermissions as $roleName => $rolePermissionNames) {
            $role = Role::query()->where('name', $roleName)->firstOrFail();
            $permissionIds = $rolePermissionNames === $allPermissionIds
                ? $allPermissionIds
                : Permission::query()->whereIn('name', $rolePermissionNames)->pluck('id')->all();

            $role->permissions()->sync($permissionIds);
        }
    }
}

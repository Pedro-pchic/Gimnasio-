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
            'payments.view' => 'Consultar pagos.',
            'payments.manage' => 'Registrar y cancelar pagos.',
            'sales.view' => 'Consultar ventas y comprobantes internos.',
            'sales.manage' => 'Registrar y cancelar ventas.',
            'renewals.view' => 'Consultar renovaciones.',
            'renewals.manage' => 'Registrar renovaciones con pago.',
            'dashboard.view' => 'Consultar el panel principal.',
            'branches.view' => 'Consultar sucursales.',
            'branches.manage' => 'Gestionar sucursales y sus servicios.',
            'services.view' => 'Consultar servicios.',
            'services.manage' => 'Gestionar servicios.',
            'clients.view' => 'Consultar clientes.',
            'clients.manage' => 'Gestionar clientes.',
            'membership-types.view' => 'Consultar tipos de membresía.',
            'membership-types.manage' => 'Gestionar tipos de membresía.',
            'benefits.view' => 'Consultar beneficios.',
            'benefits.manage' => 'Gestionar beneficios.',
            'client-memberships.view' => 'Consultar membresías de clientes.',
            'client-memberships.manage' => 'Asignar, editar y cancelar membresías de clientes.',
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
                'client-memberships.view',
                'client-memberships.manage',
                'payments.view',
                'payments.manage',
                'sales.view',
                'sales.manage',
                'renewals.view',
                'renewals.manage',
            ],
            'Recepcionista' => [
                'dashboard.view',
                'clients.view',
                'clients.manage',
                'membership-types.view',
                'client-memberships.view',
                'client-memberships.manage',
                'payments.view',
                'payments.manage',
                'sales.view',
                'sales.manage',
                'renewals.view',
                'renewals.manage',
            ],
            'Supervisor' => [
                'dashboard.view',
                'branches.view',
                'services.view',
                'clients.view',
                'membership-types.view',
                'client-memberships.view',
                'payments.view',
                'sales.view',
                'renewals.view',
            ],
            'Instructor / Coach' => [
                'dashboard.view',
                'services.view',
                'clients.view',
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

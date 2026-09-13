<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PanelRenderingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_can_render_the_audited_panel_pages(): void
    {
        $user = $this->administrator();

        foreach ($this->auditedIndexRoutes() as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertOk();
        }
    }

    public function test_administrator_can_render_the_audited_create_pages(): void
    {
        $user = $this->administrator();

        foreach ($this->auditedCreateRoutes() as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertOk();
        }
    }

    public function test_supplier_validation_error_is_rendered_on_the_create_page(): void
    {
        $user = $this->administrator();

        $this->actingAs($user)
            ->from(route('suppliers.create'))
            ->post(route('suppliers.store'), ['is_active' => true])
            ->assertRedirect(route('suppliers.create'))
            ->assertSessionHasErrors('name');

        $this->actingAs($user)
            ->followingRedirects()
            ->post(route('suppliers.store'), ['is_active' => true])
            ->assertOk()
            ->assertSee('The name field is required.');
    }

    /**
     * @return list<string>
     */
    private function auditedIndexRoutes(): array
    {
        return [
            'dashboard',
            'branches.index',
            'services.index',
            'clients.index',
            'membership-types.index',
            'benefits.index',
            'client-memberships.index',
            'renewals.index',
            'referrals.index',
            'payments.index',
            'sales.index',
            'commercial-partners.index',
            'third-party-items.index',
            'discounts.index',
            'employees.index',
            'positions.index',
            'work-shifts.index',
            'employee-attendances.index',
            'employee-bonuses.index',
            'gym-classes.index',
            'class-schedules.index',
            'class-enrollments.index',
            'inventory-items.index',
            'equipment-maintenances.index',
            'suppliers.index',
            'purchase-orders.index',
            'quality-certificates.index',
            'reports.index',
        ];
    }

    /**
     * @return list<string>
     */
    private function auditedCreateRoutes(): array
    {
        return [
            'branches.create',
            'services.create',
            'clients.create',
            'membership-types.create',
            'benefits.create',
            'client-memberships.create',
            'renewals.create',
            'referrals.create',
            'payments.create',
            'sales.create',
            'commercial-partners.create',
            'third-party-items.create',
            'discounts.create',
            'employees.create',
            'positions.create',
            'work-shifts.create',
            'employee-attendances.create',
            'employee-bonuses.create',
            'gym-classes.create',
            'class-schedules.create',
            'class-enrollments.create',
            'inventory-items.create',
            'equipment-maintenances.create',
            'suppliers.create',
            'purchase-orders.create',
            'quality-certificates.create',
        ];
    }

    private function administrator(): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);

        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', 'Administrador general')->valueOrFail('id'));

        return $user->fresh();
    }
}

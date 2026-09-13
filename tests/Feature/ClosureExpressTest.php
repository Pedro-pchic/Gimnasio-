<?php

namespace Tests\Feature;

use App\EmployeeBonusStatus;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeBonus;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\PurchaseOrderStatus;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ClosureExpressTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_services_create_page_is_available(): void
    {
        $this->actingAs($this->userWithRole('Gerente de sucursal'))
            ->get(route('services.create'))
            ->assertOk();
    }

    public function test_admin_can_open_core_create_pages(): void
    {
        $user = $this->userWithRole('Administrador general');
        foreach (['clients.create', 'services.create', 'benefits.create'] as $routeName) {
            $response = $this->actingAs($user)->get(route($routeName));
            $response->assertOk();
            if ($routeName === 'clients.create') {
                $response->assertSeeInOrder(['Inventario', 'Proveedores', 'Órdenes de compra y certificados', 'Reportes']);
                $response->assertSee(route('inventory-items.index'), false)
                    ->assertSee(route('suppliers.index'), false)
                    ->assertSee(route('purchase-orders.index'), false)
                    ->assertSee(route('reports.index'), false);
            }
        }
    }

    public function test_receiving_purchase_order_increments_inventory(): void
    {
        $user = $this->userWithRole('Gerente de sucursal');
        $branch = Branch::factory()->create();
        $item = InventoryItem::factory()->for($branch)->create(['quantity' => 5]);
        $order = PurchaseOrder::factory()->for(Supplier::factory())->for($branch)->create(['user_id' => $user->id]);
        $order->items()->create(['inventory_item_id' => $item->id, 'quantity' => 3, 'unit_cost' => 10, 'subtotal' => 30]);

        $this->actingAs($user)->patch(route('purchase-orders.receive', $order))->assertRedirect();

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'quantity' => 8]);
        $this->assertDatabaseHas('purchase_orders', ['id' => $order->id, 'status' => PurchaseOrderStatus::Received->value]);
    }

    public function test_purchase_order_cannot_be_received_twice(): void
    {
        $user = $this->userWithRole('Gerente de sucursal');
        $branch = Branch::factory()->create();
        $item = InventoryItem::factory()->for($branch)->create(['quantity' => 5]);
        $order = PurchaseOrder::factory()->for(Supplier::factory())->for($branch)->create(['user_id' => $user->id]);
        $order->items()->create(['inventory_item_id' => $item->id, 'quantity' => 2, 'unit_cost' => 10, 'subtotal' => 20]);
        $this->actingAs($user)->patch(route('purchase-orders.receive', $order));
        $this->actingAs($user)->patch(route('purchase-orders.receive', $order))->assertSessionHasErrors('purchase_order');
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'quantity' => 7]);
    }

    public function test_bonus_can_be_registered_and_approved(): void
    {
        $user = $this->userWithRole('Gerente de sucursal');
        $employee = Employee::factory()->create();
        $this->actingAs($user)->post(route('employee-bonuses.store'), ['employee_id' => $employee->id, 'period' => '2026-09', 'amount' => 125, 'reason' => 'Meta cumplida'])->assertRedirect();
        $bonus = EmployeeBonus::query()->latest('id')->firstOrFail();
        $this->actingAs($user)->patch(route('employee-bonuses.approve', $bonus))->assertRedirect();
        $this->assertDatabaseHas('employee_bonuses', ['id' => $bonus->id, 'status' => EmployeeBonusStatus::Approved->value, 'approved_by' => $user->id]);
    }

    public function test_reports_require_authentication(): void
    {
        $this->get(route('reports.index'))->assertRedirect(route('login'));
    }

    public function test_instructor_cannot_access_reports(): void
    {
        $this->actingAs($this->userWithRole('Instructor / Coach'))->get(route('reports.index'))->assertForbidden();
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->firstOrFail());

        return $user;
    }
}

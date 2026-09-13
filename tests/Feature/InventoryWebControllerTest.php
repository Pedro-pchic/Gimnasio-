<?php

namespace Tests\Feature;

use App\EquipmentMaintenanceStatus;
use App\InventoryItemStatus;
use App\InventoryItemType;
use App\InventoryMovementType;
use App\Models\Branch;
use App\Models\EquipmentMaintenance;
use App\Models\InventoryItem;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class InventoryWebControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_manager_creates_inventory_item(): void
    {
        $manager = $this->userWithRole('Gerente de sucursal');
        $branch = Branch::factory()->create();

        $this->actingAs($manager)->post(route('inventory-items.store'), $this->inventoryPayload($branch, [
            'name' => 'Bandas elásticas',
            'internal_code' => 'SUP-001',
            'quantity' => 12,
            'minimum_stock' => 4,
        ]))->assertRedirect();

        $this->assertDatabaseHas('inventory_items', [
            'branch_id' => $branch->id,
            'name' => 'Bandas elásticas',
            'internal_code' => 'SUP-001',
            'quantity' => 12,
            'minimum_stock' => 4,
            'status' => InventoryItemStatus::Active->value,
        ]);
    }

    public function test_duplicate_inventory_code_is_rejected(): void
    {
        $manager = $this->userWithRole('Gerente de sucursal');
        $branch = Branch::factory()->create();
        InventoryItem::factory()->for($branch)->create(['internal_code' => 'EQ-001']);

        $this->actingAs($manager)->post(route('inventory-items.store'), $this->inventoryPayload($branch, [
            'internal_code' => 'EQ-001',
        ]))->assertInvalid(['internal_code']);

        $this->assertSame(1, InventoryItem::query()->where('internal_code', 'EQ-001')->count());
    }

    public function test_supervisor_registers_stock_entry(): void
    {
        $supervisor = $this->userWithRole('Supervisor');
        $inventoryItem = InventoryItem::factory()->create(['quantity' => 3]);

        $this->actingAs($supervisor)->post(route('inventory-items.movements.store', $inventoryItem), [
            'type' => InventoryMovementType::Entry->value,
            'quantity' => 5,
            'reason' => 'Recepción de proveedor',
        ])->assertRedirect(route('inventory-items.movements', $inventoryItem));

        $this->assertDatabaseHas('inventory_items', ['id' => $inventoryItem->id, 'quantity' => 8]);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $inventoryItem->id,
            'type' => InventoryMovementType::Entry->value,
            'quantity' => 5,
            'user_id' => $supervisor->id,
        ]);
    }

    public function test_supervisor_registers_stock_exit(): void
    {
        $supervisor = $this->userWithRole('Supervisor');
        $inventoryItem = InventoryItem::factory()->create(['quantity' => 8]);

        $this->actingAs($supervisor)->post(route('inventory-items.movements.store', $inventoryItem), [
            'type' => InventoryMovementType::Exit->value,
            'quantity' => 3,
            'reason' => 'Uso en clase',
        ])->assertRedirect();

        $this->assertDatabaseHas('inventory_items', ['id' => $inventoryItem->id, 'quantity' => 5]);
        $this->assertDatabaseHas('inventory_movements', ['inventory_item_id' => $inventoryItem->id, 'type' => InventoryMovementType::Exit->value, 'quantity' => 3]);
    }

    public function test_stock_exit_that_exceeds_available_quantity_is_rejected(): void
    {
        $supervisor = $this->userWithRole('Supervisor');
        $inventoryItem = InventoryItem::factory()->create(['quantity' => 4]);

        $this->actingAs($supervisor)->post(route('inventory-items.movements.store', $inventoryItem), [
            'type' => InventoryMovementType::Exit->value,
            'quantity' => 5,
        ])->assertInvalid(['quantity']);

        $this->assertDatabaseHas('inventory_items', ['id' => $inventoryItem->id, 'quantity' => 4]);
        $this->assertSame(0, $inventoryItem->movements()->count());
    }

    public function test_stock_adjustment_sets_inventory_quantity_and_preserves_history(): void
    {
        $supervisor = $this->userWithRole('Supervisor');
        $inventoryItem = InventoryItem::factory()->create(['quantity' => 10]);

        $this->actingAs($supervisor)->post(route('inventory-items.movements.store', $inventoryItem), [
            'type' => InventoryMovementType::Adjustment->value,
            'quantity' => 6,
            'reason' => 'Conteo físico',
        ])->assertRedirect();

        $this->assertDatabaseHas('inventory_items', ['id' => $inventoryItem->id, 'quantity' => 6]);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $inventoryItem->id,
            'type' => InventoryMovementType::Adjustment->value,
            'quantity' => 6,
            'reason' => 'Conteo físico',
        ]);
        $this->actingAs($supervisor)->get(route('inventory-items.movements', $inventoryItem))->assertSee('Conteo físico');
    }

    public function test_manager_cannot_change_stock_without_inventory_movement(): void
    {
        $manager = $this->userWithRole('Gerente de sucursal');
        $inventoryItem = InventoryItem::factory()->create(['quantity' => 10]);

        $this->actingAs($manager)->put(route('inventory-items.update', $inventoryItem), $this->inventoryPayload($inventoryItem->branch, [
            'name' => $inventoryItem->name,
            'type' => $inventoryItem->type->value,
            'internal_code' => $inventoryItem->internal_code,
            'quantity' => 4,
            'minimum_stock' => $inventoryItem->minimum_stock,
        ]))->assertInvalid(['quantity']);

        $this->assertDatabaseHas('inventory_items', ['id' => $inventoryItem->id, 'quantity' => 10]);
        $this->assertSame(0, $inventoryItem->movements()->count());
    }

    public function test_manager_creates_pending_equipment_maintenance(): void
    {
        $manager = $this->userWithRole('Gerente de sucursal');
        $equipment = $this->equipmentItem();

        $this->actingAs($manager)->post(route('equipment-maintenances.store'), [
            'inventory_item_id' => $equipment->id,
            'scheduled_date' => '2026-09-20',
            'description' => 'Lubricación de poleas',
            'status' => EquipmentMaintenanceStatus::Pending->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('equipment_maintenances', [
            'inventory_item_id' => $equipment->id,
            'status' => EquipmentMaintenanceStatus::Pending->value,
            'description' => 'Lubricación de poleas',
        ]);
        $this->assertDatabaseHas('inventory_items', ['id' => $equipment->id, 'status' => InventoryItemStatus::Maintenance->value]);
    }

    public function test_manager_completes_pending_maintenance(): void
    {
        $manager = $this->userWithRole('Gerente de sucursal');
        $equipment = $this->equipmentItem(['status' => InventoryItemStatus::Maintenance->value]);
        $maintenance = EquipmentMaintenance::factory()->for($equipment, 'inventoryItem')->create([
            'scheduled_date' => '2026-09-15',
            'status' => EquipmentMaintenanceStatus::Pending->value,
        ]);

        $this->actingAs($manager)->patch(route('equipment-maintenances.complete', $maintenance), [
            'performed_date' => '2026-09-16',
        ])->assertRedirect();

        $this->assertDatabaseHas('equipment_maintenances', [
            'id' => $maintenance->id,
            'status' => EquipmentMaintenanceStatus::Completed->value,
        ]);
        $this->assertSame('2026-09-16', $maintenance->fresh()->performed_date->toDateString());
        $this->assertDatabaseHas('inventory_items', ['id' => $equipment->id, 'status' => InventoryItemStatus::Active->value]);
    }

    public function test_inventory_permissions_limit_management_and_movements(): void
    {
        $supervisor = $this->userWithRole('Supervisor');
        $receptionist = $this->userWithRole('Recepcionista');
        $instructor = $this->userWithRole('Instructor / Coach');
        $inventoryItem = InventoryItem::factory()->create();

        $this->actingAs($supervisor)->get(route('inventory-items.index'))->assertOk();
        $this->actingAs($supervisor)->get(route('inventory-items.create'))->assertForbidden();
        $this->actingAs($receptionist)->get(route('inventory-items.index'))->assertOk();
        $this->actingAs($receptionist)->post(route('inventory-items.movements.store', $inventoryItem), ['type' => 'entry', 'quantity' => 1])->assertForbidden();
        $this->actingAs($instructor)->get(route('equipment-maintenances.index'))->assertOk();
        $this->actingAs($instructor)->get(route('equipment-maintenances.create'))->assertForbidden();
    }

    private function equipmentItem(array $attributes = []): InventoryItem
    {
        return InventoryItem::factory()->create(array_merge([
            'type' => InventoryItemType::Equipment->value,
            'quantity' => null,
            'minimum_stock' => null,
            'description' => 'Equipo para entrenamiento de fuerza.',
            'maintenance_required' => true,
        ], $attributes));
    }

    /**
     * @return array<string, mixed>
     */
    private function inventoryPayload(Branch $branch, array $attributes = []): array
    {
        return array_merge([
            'branch_id' => $branch->id,
            'name' => 'Mancuernas',
            'type' => InventoryItemType::Supply->value,
            'internal_code' => 'INV-001',
            'quantity' => 5,
            'minimum_stock' => 1,
            'status' => InventoryItemStatus::Active->value,
            'description' => 'Insumo operativo.',
            'maintenance_required' => false,
        ], $attributes);
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user;
    }
}

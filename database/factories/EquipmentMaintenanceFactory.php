<?php

namespace Database\Factories;

use App\EquipmentMaintenanceStatus;
use App\InventoryItemType;
use App\Models\EquipmentMaintenance;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EquipmentMaintenance>
 */
class EquipmentMaintenanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory()->state([
                'type' => InventoryItemType::Equipment->value,
                'quantity' => null,
                'minimum_stock' => null,
                'maintenance_required' => true,
            ]),
            'scheduled_date' => fake()->date(),
            'performed_date' => null,
            'description' => fake()->sentence(),
            'status' => EquipmentMaintenanceStatus::Pending->value,
            'cost' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\InventoryItemStatus;
use App\InventoryItemType;
use App\Models\Branch;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => fake()->unique()->words(3, true),
            'type' => InventoryItemType::Product->value,
            'internal_code' => 'INV-'.fake()->unique()->numerify('######'),
            'quantity' => 10,
            'minimum_stock' => 3,
            'status' => InventoryItemStatus::Active->value,
            'notes' => fake()->optional()->sentence(),
            'description' => fake()->optional()->sentence(),
            'acquisition_date' => fake()->optional()->date(),
            'brand' => fake()->optional()->company(),
            'model' => fake()->optional()->bothify('MOD-###'),
            'serial' => fake()->optional()->bothify('SER-#####'),
            'maintenance_required' => false,
        ];
    }
}

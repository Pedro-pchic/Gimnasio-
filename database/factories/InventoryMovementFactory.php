<?php

namespace Database\Factories;

use App\InventoryMovementType;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'type' => InventoryMovementType::Entry->value,
            'quantity' => 5,
            'reason' => fake()->optional()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}

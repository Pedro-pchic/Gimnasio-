<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrderStatus::Pending->value,
            'total' => 0,
            'received_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

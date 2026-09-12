<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Sale;
use App\Models\User;
use App\SaleStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'sale_date' => fake()->date(),
            'subtotal' => 100,
            'discount' => 0,
            'total' => 100,
            'status' => SaleStatus::Completed,
        ];
    }
}

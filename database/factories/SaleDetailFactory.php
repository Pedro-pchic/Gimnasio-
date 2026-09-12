<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\SaleDetailType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleDetail>
 */
class SaleDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'concept_type' => SaleDetailType::Other,
            'concept_reference_id' => null,
            'description' => fake()->sentence(3),
            'quantity' => 1,
            'unit_price' => 100,
            'subtotal' => 100,
        ];
    }
}

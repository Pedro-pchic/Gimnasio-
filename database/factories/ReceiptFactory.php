<?php

namespace Database\Factories;

use App\Models\Receipt;
use App\Models\Sale;
use App\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receipt>
 */
class ReceiptFactory extends Factory
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
            'number' => 'COMP-'.fake()->unique()->numerify('######'),
            'issued_at' => fake()->date(),
            'payment_method' => PaymentMethod::Cash,
        ];
    }
}

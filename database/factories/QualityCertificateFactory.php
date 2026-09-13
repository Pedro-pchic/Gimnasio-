<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\QualityCertificate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QualityCertificate>
 */
class QualityCertificateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'number' => 'CERT-'.fake()->unique()->numerify('######'),
            'issued_date' => now()->toDateString(),
            'expires_date' => now()->addYear()->toDateString(),
            'status' => 'valid',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

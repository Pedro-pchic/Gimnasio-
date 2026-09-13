<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Payment;
use App\Models\User;
use App\PaymentMethod;
use App\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
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
            'user_id' => User::factory(),
            'sale_id' => null,
            'client_membership_id' => null,
            'payment_date' => fake()->date(),
            'amount' => fake()->randomFloat(2, 1, 1000),
            'payment_method' => PaymentMethod::Cash,
            'reference' => fake()->optional()->bothify('REF-#####'),
            'status' => PaymentStatus::Paid,
            'observations' => fake()->optional()->sentence(),
        ];
    }
}

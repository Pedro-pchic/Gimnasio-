<?php

namespace Database\Factories;

use App\CommercialPartnerType;
use App\Models\CommercialPartner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommercialPartner>
 */
class CommercialPartnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'type' => fake()->randomElement(CommercialPartnerType::cases())->value,
            'contact' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'observations' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}

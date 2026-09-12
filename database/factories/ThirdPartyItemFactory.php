<?php

namespace Database\Factories;

use App\Models\CommercialPartner;
use App\Models\ThirdPartyItem;
use App\ThirdPartyItemType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ThirdPartyItem>
 */
class ThirdPartyItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'third_party_id' => CommercialPartner::factory(),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'type' => fake()->randomElement(ThirdPartyItemType::cases())->value,
            'base_price' => fake()->randomFloat(2, 10, 500),
            'is_active' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Referral;
use App\Models\ReferralRewardUse;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReferralRewardUse>
 */
class ReferralRewardUseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referral_id' => Referral::factory(),
            'sale_id' => Sale::factory(),
            'amount' => fake()->randomFloat(2, 1, 100),
        ];
    }
}

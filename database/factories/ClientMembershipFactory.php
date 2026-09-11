<?php

namespace Database\Factories;

use App\ClientMembershipStatus;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\MembershipType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientMembership>
 */
class ClientMembershipFactory extends Factory
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
            'membership_type_id' => MembershipType::factory(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'applied_price' => fake()->randomFloat(2, 0, 1000),
            'status' => ClientMembershipStatus::Pending,
            'observations' => fake()->optional()->sentence(),
        ];
    }
}

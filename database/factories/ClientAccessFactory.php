<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientAccess;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientAccess>
 */
class ClientAccessFactory extends Factory
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
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => null,
        ];
    }
}

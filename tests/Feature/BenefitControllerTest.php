<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BenefitControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_payload_creates_configurable_benefit(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/benefits', [
            'name' => 'Masajes mensuales',
            'description' => 'Cantidad configurable por período.',
            'benefit_type' => 'usage_limit',
            'value' => 3,
            'usage_limit' => 3,
            'usage_period' => 'month',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.usage_limit', 3)
            ->assertJsonPath('data.value', '3.00');

        $this->assertDatabaseHas('benefits', [
            'name' => 'Masajes mensuales',
            'benefit_type' => 'usage_limit',
            'usage_limit' => 3,
        ]);
    }
}

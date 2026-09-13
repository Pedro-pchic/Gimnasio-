<?php

namespace Tests\Feature;

use App\Models\Benefit;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MembershipTypeControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_payload_creates_membership_type_and_associates_benefits(): void
    {
        $user = User::factory()->create();
        $benefits = Benefit::factory()->count(2)->create();

        $response = $this->actingAs($user)->postJson('/api/v1/membership-types', [
            'name' => 'Premium',
            'description' => 'Membresía de referencia.',
            'reference_price' => 350,
            'benefit_ids' => $benefits->modelKeys(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Premium')
            ->assertJsonPath('data.reference_price', '350.00');

        $this->assertDatabaseHas('membership_types', [
            'name' => 'Premium',
            'reference_price' => 350,
        ]);
        $this->assertDatabaseHas('benefit_membership_type', [
            'membership_type_id' => $response->json('data.id'),
            'benefit_id' => $benefits->first()->id,
        ]);
        $this->assertDatabaseHas('benefit_membership_type', [
            'membership_type_id' => $response->json('data.id'),
            'benefit_id' => $benefits->last()->id,
        ]);
    }

    public function test_returns_422_when_reference_price_is_negative(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/membership-types', [
            'name' => 'Inválida',
            'reference_price' => -1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('reference_price')
            ->assertJsonPath('errors.reference_price.0', 'The reference price field must be at least 0.');
    }
}

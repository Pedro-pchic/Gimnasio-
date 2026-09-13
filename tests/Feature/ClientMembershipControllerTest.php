<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\MembershipType;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ClientMembershipControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_payload_assigns_membership_to_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for(Branch::factory())->create();
        $membershipType = MembershipType::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/client-memberships', [
            'client_id' => $client->id,
            'membership_type_id' => $membershipType->id,
            'start_date' => '2026-09-10',
            'end_date' => '2026-10-10',
            'applied_price' => 350,
            'status' => 'active',
            'observations' => 'Precio acordado al momento de la inscripción.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.client_id', $client->id)
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('client_memberships', [
            'client_id' => $client->id,
            'membership_type_id' => $membershipType->id,
            'applied_price' => 350,
            'status' => 'active',
        ]);
    }

    public function test_returns_422_when_membership_end_date_is_before_start_date(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for(Branch::factory())->create();
        $membershipType = MembershipType::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/client-memberships', [
            'client_id' => $client->id,
            'membership_type_id' => $membershipType->id,
            'start_date' => '2026-10-10',
            'end_date' => '2026-09-10',
            'applied_price' => 350,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('end_date');
    }

    public function test_destroy_cancels_membership_without_removing_it(): void
    {
        $user = User::factory()->create();
        $membership = ClientMembership::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->deleteJson("/api/v1/client-memberships/{$membership->id}");

        $response->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('client_memberships', [
            'id' => $membership->id,
            'status' => 'cancelled',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BranchControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_returns_401_when_the_request_is_not_authenticated(): void
    {
        $this->postJson('/api/v1/branches', [])
            ->assertUnauthorized();
    }

    public function test_valid_payload_creates_branch_and_associates_services(): void
    {
        $user = User::factory()->create();
        $services = Service::factory()->count(2)->create();

        $response = $this->actingAs($user)->postJson('/api/v1/branches', [
            'name' => 'Sucursal Central',
            'code' => 'CENTRAL',
            'address' => 'Zona 1',
            'phone' => '5555-0101',
            'opening_time' => '06:00',
            'closing_time' => '21:00',
            'service_ids' => $services->modelKeys(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.code', 'CENTRAL');

        $this->assertDatabaseHas('branches', [
            'code' => 'CENTRAL',
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('branch_service', [
            'branch_id' => $response->json('data.id'),
            'service_id' => $services->first()->id,
        ]);
        $this->assertDatabaseHas('branch_service', [
            'branch_id' => $response->json('data.id'),
            'service_id' => $services->last()->id,
        ]);
    }

    public function test_returns_422_when_required_branch_fields_are_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/branches', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'code'])
            ->assertJsonPath('errors.name.0', 'The name field is required.');
    }
}

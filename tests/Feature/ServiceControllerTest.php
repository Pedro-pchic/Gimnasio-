<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_payload_creates_service(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/services', [
            'name' => 'Piscina',
            'description' => 'Acceso al área de piscina.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Piscina');

        $this->assertDatabaseHas('services', [
            'name' => 'Piscina',
            'is_active' => 1,
        ]);
    }

    public function test_destroy_deactivates_service_without_removing_it(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/services/{$service->id}");

        $response->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'is_active' => 0,
        ]);
    }
}

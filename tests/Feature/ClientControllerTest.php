<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_payload_creates_client_in_its_main_branch(): void
    {
        $user = User::factory()->create();
        $branch = Branch::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/clients', [
            'branch_id' => $branch->id,
            'code' => 'CLI-000001',
            'first_name' => 'Ana',
            'last_name' => 'López',
            'birth_date' => '1995-04-15',
            'phone' => '5555-1000',
            'email' => 'ana@example.com',
            'address' => 'Zona 10',
            'registration_date' => '2026-09-10',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.branch_id', $branch->id)
            ->assertJsonPath('data.code', 'CLI-000001');

        $this->assertDatabaseHas('clients', [
            'branch_id' => $branch->id,
            'code' => 'CLI-000001',
            'email' => 'ana@example.com',
        ]);
    }

    public function test_returns_422_when_client_email_or_branch_is_invalid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/clients', [
            'branch_id' => 999,
            'code' => 'CLI-000002',
            'first_name' => 'Ana',
            'last_name' => 'López',
            'email' => 'not-an-email',
            'registration_date' => '2026-09-10',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id', 'email'])
            ->assertJsonPath('errors.email.0', 'The email field must be a valid email address.');
    }
}

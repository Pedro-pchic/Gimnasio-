<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ClientWebControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_receptionist_creates_client(): void
    {
        $user = $this->userWithRole('Recepcionista');
        $branch = Branch::factory()->create();

        $response = $this->actingAs($user)->post('/clients', [
            'branch_id' => $branch->id,
            'code' => 'CLI-000001',
            'first_name' => 'Ana',
            'last_name' => 'López',
            'birth_date' => '1995-04-15',
            'phone' => '5555-1000',
            'email' => 'ana@example.com',
            'address' => 'Zona 10',
            'registration_date' => '2026-09-10',
            'is_active' => '1',
        ]);
        $client = Client::query()->where('code', 'CLI-000001')->firstOrFail();

        $response->assertRedirectToRoute('clients.show', $client);
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'branch_id' => $branch->id,
            'email' => 'ana@example.com',
        ]);
    }

    public function test_receptionist_updates_and_deactivates_client(): void
    {
        $user = $this->userWithRole('Recepcionista');
        $client = Client::factory()->create(['is_active' => true]);

        $this->actingAs($user)
            ->put("/clients/{$client->id}", ['phone' => '5555-2000'])
            ->assertRedirectToRoute('clients.show', $client);
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'phone' => '5555-2000']);

        $this->actingAs($user)
            ->delete("/clients/{$client->id}")
            ->assertRedirectToRoute('clients.index');
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'is_active' => 0]);
    }

    public function test_client_creation_shows_main_required_field_errors(): void
    {
        $user = $this->userWithRole('Recepcionista');

        $this->actingAs($user)
            ->post('/clients', [])
            ->assertInvalid([
                'branch_id' => 'The branch id field is required.',
                'code' => 'The code field is required.',
                'first_name' => 'The first name field is required.',
            ]);
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user->fresh();
    }
}

<?php

namespace Tests\Feature;

use App\ClientMembershipStatus;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\MembershipType;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BiometricAccessControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_simulated_fingerprint_alternates_authorized_client_entry_and_exit(): void
    {
        $this->travelTo('2026-09-14 08:15:00');

        try {
            $administrator = $this->administrator();
            $client = Client::factory()->create(['first_name' => 'Juan', 'last_name' => 'Pérez']);
            ClientMembership::factory()
                ->for($client)
                ->for(MembershipType::factory()->create(['name' => 'Premium']))
                ->create([
                    'start_date' => '2026-09-01',
                    'end_date' => '2026-10-01',
                    'status' => ClientMembershipStatus::Active,
                ]);

            $this->actingAs($administrator)
                ->followingRedirects()
                ->post(route('biometric-access.store'), ['client_id' => $client->id])
                ->assertSee('CONTROL DE ACCESO BIOMÉTRICO')
                ->assertSee('ENTRADA AUTORIZADA');

            $this->assertDatabaseHas('client_accesses', [
                'client_id' => $client->id,
                'checked_in_at' => '2026-09-14 08:15:00',
                'checked_out_at' => null,
            ]);

            $this->travelTo('2026-09-14 09:15:00');

            $this->actingAs($administrator)
                ->followingRedirects()
                ->post(route('biometric-access.store'), ['client_id' => $client->id])
                ->assertSee('SALIDA REGISTRADA');

            $this->assertDatabaseHas('client_accesses', [
                'client_id' => $client->id,
                'checked_out_at' => '2026-09-14 09:15:00',
            ]);
        } finally {
            $this->travelBack();
        }
    }

    private function administrator(): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $administrator = User::factory()->create();
        $administrator->roles()->attach(Role::query()->where('name', 'Administrador general')->valueOrFail('id'));

        return $administrator->fresh();
    }
}

<?php

namespace Tests\Feature;

use App\ClientMembershipStatus;
use App\Models\Branch;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\MembershipType;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ClientMembershipWebControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_receptionist_assigns_membership_to_client(): void
    {
        $user = $this->userWithRole('Recepcionista');
        $client = Client::factory()->for(Branch::factory())->create();
        $membershipType = MembershipType::factory()->create();

        $response = $this->actingAs($user)->post('/client-memberships', [
            'client_id' => $client->id,
            'membership_type_id' => $membershipType->id,
            'start_date' => '2026-09-10',
            'end_date' => '2026-10-10',
            'applied_price' => 350,
            'status' => ClientMembershipStatus::Active->value,
            'observations' => 'Precio acordado al momento de la inscripción.',
        ]);
        $clientMembership = ClientMembership::query()->firstOrFail();

        $response->assertRedirectToRoute('client-memberships.show', $clientMembership);
        $this->assertDatabaseHas('client_memberships', [
            'id' => $clientMembership->id,
            'client_id' => $client->id,
            'membership_type_id' => $membershipType->id,
            'status' => 'active',
        ]);
    }

    public function test_receptionist_can_cancel_membership_without_deleting_history(): void
    {
        $user = $this->userWithRole('Recepcionista');
        $clientMembership = ClientMembership::factory()->create([
            'status' => ClientMembershipStatus::Active,
        ]);

        $this->actingAs($user)
            ->delete("/client-memberships/{$clientMembership->id}")
            ->assertRedirectToRoute('client-memberships.index');

        $this->assertDatabaseHas('client_memberships', [
            'id' => $clientMembership->id,
            'status' => ClientMembershipStatus::Cancelled->value,
        ]);
    }

    public function test_membership_assignment_rejects_end_date_before_start_date(): void
    {
        $user = $this->userWithRole('Recepcionista');
        $client = Client::factory()->for(Branch::factory())->create();
        $membershipType = MembershipType::factory()->create();

        $this->actingAs($user)
            ->post('/client-memberships', [
                'client_id' => $client->id,
                'membership_type_id' => $membershipType->id,
                'start_date' => '2026-10-10',
                'end_date' => '2026-09-10',
                'applied_price' => 350,
            ])
            ->assertInvalid(['end_date' => 'The end date field must be a date after or equal to start date.']);
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user->fresh();
    }
}

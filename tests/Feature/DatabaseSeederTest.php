<?php

namespace Tests\Feature;

use App\Models\Benefit;
use App\Models\Branch;
use App\Models\Client;
use App\Models\GymClass;
use App\Models\GymClassEnrollment;
use App\Models\GymClassSchedule;
use App\Models\MembershipType;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_database_seeder_creates_required_initial_records_and_relationships(): void
    {
        $this->seed(DatabaseSeeder::class);

        $branch = Branch::query()->where('code', 'CENTRAL')->firstOrFail();
        $client = Client::query()->where('code', 'CLI-000001')->firstOrFail();
        $membershipType = MembershipType::query()->where('name', 'Premium')->firstOrFail();
        $benefit = Benefit::query()->where('name', 'Sesiones de masaje')->firstOrFail();
        $role = Role::query()->where('name', 'Administrador general')->firstOrFail();
        $service = Service::query()->where('name', 'Gimnasio')->firstOrFail();
        $user = User::query()->where('email', 'test@example.com')->firstOrFail();
        $swimming = GymClass::query()->where('name', 'Natación principiantes')->firstOrFail();
        $swimmingSchedule = GymClassSchedule::query()->where('gym_class_id', $swimming->getKey())->firstOrFail();
        $swimmingEnrollment = GymClassEnrollment::query()
            ->where('client_id', $client->getKey())
            ->where('gym_class_schedule_id', $swimmingSchedule->getKey())
            ->firstOrFail();

        $this->assertModelExists($branch);
        $this->assertModelExists($client);
        $this->assertModelExists($benefit);
        $this->assertModelExists($user);
        $this->assertModelExists($swimming);
        $this->assertDatabaseHas('permissions', ['name' => 'dashboard.view']);
        $this->assertDatabaseHas('branch_service', [
            'branch_id' => $branch->getKey(),
            'service_id' => $service->getKey(),
        ]);
        $this->assertDatabaseHas('benefit_membership_type', [
            'benefit_id' => $benefit->getKey(),
            'membership_type_id' => $membershipType->getKey(),
        ]);
        $this->assertDatabaseHas('client_memberships', [
            'client_id' => $client->getKey(),
            'membership_type_id' => $membershipType->getKey(),
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->getKey(),
            'user_id' => $user->getKey(),
        ]);
        $this->assertSame('2026-09-14', $swimmingEnrollment->enrollment_date->toDateString());
        $this->assertSame('enrolled', $swimmingEnrollment->status->value);
    }
}

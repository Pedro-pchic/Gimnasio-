<?php

namespace Tests\Feature;

use App\ClientMembershipStatus;
use App\GymClassEnrollmentStatus;
use App\Models\Branch;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\GymClass;
use App\Models\GymClassEnrollment;
use App\Models\GymClassSchedule;
use App\Models\MembershipType;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GymClassEnrollmentWebControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_receptionist_enrolls_an_active_member_and_client_history_renders_the_activity(): void
    {
        $user = $this->userWithRole('Recepcionista');
        [$client, $schedule] = $this->activeClientAndMondaySchedule();

        $response = $this->actingAs($user)->post('/class-enrollments', [
            'client_id' => $client->id,
            'gym_class_schedule_id' => $schedule->id,
            'enrollment_date' => '2026-09-21',
        ]);
        $enrollment = GymClassEnrollment::query()->firstOrFail();

        $response->assertRedirectToRoute('class-enrollments.show', $enrollment);
        $this->assertSame(GymClassEnrollmentStatus::Enrolled, $enrollment->status);
        $this->actingAs($user)->get("/clients/{$client->id}")->assertSee($schedule->gymClass->name);
    }

    public function test_enrollment_rejects_a_client_without_a_valid_membership(): void
    {
        $user = $this->userWithRole('Recepcionista');
        $branch = Branch::factory()->create();
        $client = Client::factory()->for($branch)->create();
        $schedule = GymClassSchedule::factory()->for(GymClass::factory()->for($branch))->create([
            'day_of_week' => 'monday',
        ]);

        $this->actingAs($user)
            ->post('/class-enrollments', [
                'client_id' => $client->id,
                'gym_class_schedule_id' => $schedule->id,
                'enrollment_date' => '2026-09-21',
            ])
            ->assertInvalid(['client_id']);

        $this->assertDatabaseCount('gym_class_enrollments', 0);
    }

    public function test_enrollment_rejects_duplicate_participation_for_the_same_schedule_and_date(): void
    {
        $user = $this->userWithRole('Recepcionista');
        [$client, $schedule] = $this->activeClientAndMondaySchedule();
        GymClassEnrollment::factory()->for($client)->for($schedule)->create([
            'enrollment_date' => '2026-09-21',
            'status' => GymClassEnrollmentStatus::Enrolled,
        ]);

        $this->actingAs($user)
            ->post('/class-enrollments', [
                'client_id' => $client->id,
                'gym_class_schedule_id' => $schedule->id,
                'enrollment_date' => '2026-09-21',
            ])
            ->assertInvalid(['client_id']);

        $this->assertDatabaseCount('gym_class_enrollments', 1);
    }

    public function test_enrollment_rejects_a_second_active_participant_when_capacity_is_full(): void
    {
        $user = $this->userWithRole('Recepcionista');
        [$firstClient, $schedule] = $this->activeClientAndMondaySchedule(1);
        $secondClient = $this->activeClientForBranch($schedule->gymClass->branch);
        GymClassEnrollment::factory()->for($firstClient)->for($schedule)->create([
            'enrollment_date' => '2026-09-21',
            'status' => GymClassEnrollmentStatus::Enrolled,
        ]);

        $this->actingAs($user)
            ->post('/class-enrollments', [
                'client_id' => $secondClient->id,
                'gym_class_schedule_id' => $schedule->id,
                'enrollment_date' => '2026-09-21',
            ])
            ->assertInvalid(['gym_class_schedule_id']);

        $this->assertDatabaseCount('gym_class_enrollments', 1);
    }

    public function test_enrollment_rejects_a_standard_member_from_a_premium_activity(): void
    {
        $user = $this->userWithRole('Recepcionista');
        $branch = Branch::factory()->create();
        $client = $this->activeClientForBranch($branch);
        $client->memberships()->firstOrFail()->membershipType->update(['name' => 'Básica']);
        $schedule = GymClassSchedule::factory()->for(GymClass::factory()->for($branch)->state([
            'requires_premium' => true,
        ]))->create([
            'day_of_week' => 'monday',
            'maximum_capacity' => 10,
        ]);

        $this->actingAs($user)
            ->post('/class-enrollments', [
                'client_id' => $client->id,
                'gym_class_schedule_id' => $schedule->id,
                'enrollment_date' => '2026-09-21',
            ])
            ->assertInvalid(['client_id']);

        $this->assertDatabaseCount('gym_class_enrollments', 0);
    }

    public function test_cancellation_keeps_history_and_instructor_marks_attendance(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $instructor = $this->userWithRole('Instructor / Coach');
        [$client, $schedule] = $this->activeClientAndMondaySchedule();
        $cancelledEnrollment = GymClassEnrollment::factory()->for($client)->for($schedule)->create();
        $attendanceEnrollment = GymClassEnrollment::factory()->for($this->activeClientForBranch($schedule->gymClass->branch))->for($schedule)->create([
            'enrollment_date' => '2026-09-21',
        ]);

        $this->actingAs($receptionist)
            ->patch("/class-enrollments/{$cancelledEnrollment->id}/cancel")
            ->assertRedirectToRoute('class-enrollments.show', $cancelledEnrollment);
        $this->actingAs($instructor)
            ->patch("/class-enrollments/{$attendanceEnrollment->id}/attendance")
            ->assertRedirectToRoute('class-enrollments.show', $attendanceEnrollment);

        $this->assertDatabaseHas('gym_class_enrollments', ['id' => $cancelledEnrollment->id, 'status' => GymClassEnrollmentStatus::Cancelled->value]);
        $this->assertDatabaseHas('gym_class_enrollments', ['id' => $attendanceEnrollment->id, 'status' => GymClassEnrollmentStatus::Attended->value]);
    }

    private function activeClientAndMondaySchedule(int $capacity = 20): array
    {
        $branch = Branch::factory()->create();
        $client = $this->activeClientForBranch($branch);
        $schedule = GymClassSchedule::factory()->for(GymClass::factory()->for($branch)->state([
            'maximum_capacity' => $capacity,
        ]))->create([
            'day_of_week' => 'monday',
            'maximum_capacity' => $capacity,
        ]);

        return [$client, $schedule];
    }

    private function activeClientForBranch(Branch $branch): Client
    {
        $client = Client::factory()->for($branch)->create(['is_active' => true]);
        $membershipType = MembershipType::factory()->create();
        ClientMembership::factory()->for($client)->for($membershipType)->create([
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'status' => ClientMembershipStatus::Active,
        ]);

        return $client;
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user->fresh();
    }
}

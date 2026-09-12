<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\GymClass;
use App\Models\GymClassSchedule;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GymClassScheduleWebControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_manager_creates_a_recurrent_schedule(): void
    {
        $user = $this->userWithRole('Gerente de sucursal');
        $gymClass = GymClass::factory()->for(Branch::factory())->create();

        $response = $this->actingAs($user)->post('/class-schedules', [
            'gym_class_id' => $gymClass->id,
            'day_of_week' => 'monday',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'is_active' => true,
        ]);
        $schedule = GymClassSchedule::query()->firstOrFail();

        $response->assertRedirectToRoute('gym-classes.show', $gymClass);
        $this->assertSame('monday', $schedule->day_of_week);
        $this->assertTrue($schedule->is_active);
    }

    public function test_schedule_creation_rejects_an_end_time_before_the_start_time(): void
    {
        $user = $this->userWithRole('Gerente de sucursal');
        $gymClass = GymClass::factory()->for(Branch::factory())->create();

        $this->actingAs($user)
            ->post('/class-schedules', [
                'gym_class_id' => $gymClass->id,
                'day_of_week' => 'monday',
                'start_time' => '09:00',
                'end_time' => '08:00',
            ])
            ->assertInvalid(['end_time']);

        $this->assertDatabaseCount('gym_class_schedules', 0);
    }

    public function test_schedule_index_filters_by_branch(): void
    {
        $user = $this->userWithRole('Supervisor');
        $selectedBranch = Branch::factory()->create(['name' => 'Sucursal Norte']);
        $otherBranch = Branch::factory()->create(['name' => 'Sucursal Sur']);
        $selectedSchedule = GymClassSchedule::factory()->for(GymClass::factory()->for($selectedBranch))->create();
        $otherSchedule = GymClassSchedule::factory()->for(GymClass::factory()->for($otherBranch))->create();

        $this->actingAs($user)
            ->get("/class-schedules?branch_id={$selectedBranch->id}")
            ->assertSee($selectedSchedule->gymClass->name)
            ->assertDontSee($otherSchedule->gymClass->name);
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user->fresh();
    }
}

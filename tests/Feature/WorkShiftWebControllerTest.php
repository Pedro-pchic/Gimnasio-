<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\WorkShift;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WorkShiftWebControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_manager_creates_an_overnight_work_shift(): void
    {
        $manager = $this->userWithRole('Gerente de sucursal');

        $this->actingAs($manager)
            ->post('/work-shifts', [
                'name' => 'Nocturno operativo',
                'start_time' => '22:00',
                'end_time' => '06:00',
                'is_active' => true,
            ])
            ->assertRedirectToRoute('work-shifts.index');

        $workShift = WorkShift::query()->where('name', 'Nocturno operativo')->firstOrFail();

        $this->assertTrue($workShift->spansMidnight());
        $this->assertDatabaseHas('work_shifts', ['id' => $workShift->id, 'is_active' => true]);
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user->fresh();
    }
}

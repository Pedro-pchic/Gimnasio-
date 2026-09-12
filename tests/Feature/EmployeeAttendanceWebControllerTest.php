<?php

namespace Tests\Feature;

use App\EmployeeAttendanceStatus;
use App\EmployeeStatus;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeShiftAssignment;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkShift;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class EmployeeAttendanceWebControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_receptionist_registers_an_employee_entry_with_the_applicable_shift(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $employee = Employee::factory()->create();
        $workShift = WorkShift::factory()->create();
        EmployeeShiftAssignment::factory()->for($employee)->for($workShift)->create([
            'effective_from' => '2026-09-01',
        ]);

        $response = $this->actingAs($receptionist)->post('/employee-attendances', [
            'employee_id' => $employee->id,
            'attendance_date' => '2026-09-14',
            'entry_time' => '08:15',
            'observations' => 'Entrada de prueba.',
        ]);
        $attendance = EmployeeAttendance::query()->firstOrFail();

        $response->assertRedirectToRoute('employee-attendances.show', $attendance);
        $this->assertDatabaseHas('employee_attendances', [
            'id' => $attendance->id,
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'work_shift_id' => $workShift->id,
            'status' => EmployeeAttendanceStatus::Present->value,
            'registration_method' => 'manual',
        ]);
    }

    public function test_employee_cannot_register_a_second_open_entry(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $employee = Employee::factory()->create();
        EmployeeAttendance::factory()->for($employee)->for($employee->branch)->create();

        $this->actingAs($receptionist)
            ->post('/employee-attendances', [
                'employee_id' => $employee->id,
                'attendance_date' => '2026-09-14',
                'entry_time' => '09:00',
            ])
            ->assertInvalid(['employee_id']);

        $this->assertDatabaseCount('employee_attendances', 1);
    }

    public function test_employee_exit_closes_the_open_attendance_and_calculates_worked_hours(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $employee = Employee::factory()->create();
        $attendance = EmployeeAttendance::factory()->for($employee)->for($employee->branch)->create([
            'attendance_date' => '2026-09-14',
            'entry_time' => '08:15:00',
        ]);

        $this->actingAs($receptionist)
            ->patch("/employee-attendances/{$attendance->id}/checkout", [
                'exit_time' => '16:27',
                'observations' => 'Salida registrada.',
            ])
            ->assertRedirectToRoute('employee-attendances.show', $attendance);

        $attendance->refresh();

        $this->assertSame(EmployeeAttendanceStatus::Finalized, $attendance->status);
        $this->assertSame('8 h 12 min', $attendance->workedDuration());
        $this->assertDatabaseHas('employee_attendances', [
            'id' => $attendance->id,
            'exit_time' => '16:27',
            'status' => EmployeeAttendanceStatus::Finalized->value,
        ]);
    }

    public function test_checkout_rejects_an_attendance_that_is_already_closed(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $attendance = EmployeeAttendance::factory()->create([
            'exit_time' => '17:00:00',
            'status' => EmployeeAttendanceStatus::Finalized,
        ]);

        $this->actingAs($receptionist)
            ->patch("/employee-attendances/{$attendance->id}/checkout", ['exit_time' => '18:00'])
            ->assertInvalid(['employee_attendance']);

        $this->assertDatabaseHas('employee_attendances', ['id' => $attendance->id, 'exit_time' => '17:00:00']);
    }

    public function test_overnight_shift_allows_a_next_day_exit_and_calculates_eight_hours(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $employee = Employee::factory()->create();
        $workShift = WorkShift::factory()->create(['start_time' => '22:00:00', 'end_time' => '06:00:00']);
        $attendance = EmployeeAttendance::factory()->for($employee)->for($employee->branch)->for($workShift)->create([
            'attendance_date' => '2026-09-14',
            'entry_time' => '22:00:00',
        ]);

        $this->actingAs($receptionist)
            ->patch("/employee-attendances/{$attendance->id}/checkout", ['exit_time' => '06:00'])
            ->assertRedirectToRoute('employee-attendances.show', $attendance);

        $attendance->refresh();

        $this->assertSame('8 h 00 min', $attendance->workedDuration());
    }

    public function test_inactive_employee_cannot_register_an_entry(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $employee = Employee::factory()->inactive()->create();

        $this->actingAs($receptionist)
            ->post('/employee-attendances', [
                'employee_id' => $employee->id,
                'attendance_date' => '2026-09-14',
                'entry_time' => '08:00',
            ])
            ->assertInvalid(['employee_id']);

        $this->assertDatabaseCount('employee_attendances', 0);
        $this->assertSame(EmployeeStatus::Inactive, $employee->status);
    }

    public function test_attendance_history_filters_by_date(): void
    {
        $supervisor = $this->userWithRole('Supervisor');
        $employee = Employee::factory()->create();
        EmployeeAttendance::factory()->for($employee)->for($employee->branch)->create(['attendance_date' => '2026-09-14']);
        EmployeeAttendance::factory()->for($employee)->for($employee->branch)->create(['attendance_date' => '2026-09-15', 'exit_time' => '17:00:00', 'status' => EmployeeAttendanceStatus::Finalized]);

        $this->actingAs($supervisor)
            ->get('/employee-attendances?attendance_date=2026-09-14')
            ->assertSee('14/09/2026')
            ->assertDontSee('15/09/2026');
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user->fresh();
    }
}

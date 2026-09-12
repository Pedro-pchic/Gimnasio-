<?php

namespace Database\Seeders;

use App\EmployeeStatus;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\Position;
use App\Models\User;
use App\Models\WorkShift;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::query()->where('code', 'CENTRAL')->first();

        if ($branch === null) {
            return;
        }

        $positionIds = Position::query()->pluck('id', 'name');
        $employees = [
            ['code' => 'EMP-000001', 'first_name' => 'Ana', 'last_name' => 'Gerente', 'position' => 'Gerente', 'phone' => '5555-0201', 'email' => 'ana.gerente@example.com', 'user_id' => User::query()->where('email', 'test@example.com')->value('id')],
            ['code' => 'EMP-000002', 'first_name' => 'Luis', 'last_name' => 'Recepción', 'position' => 'Recepcionista', 'phone' => '5555-0202', 'email' => 'luis.recepcion@example.com', 'user_id' => null],
            ['code' => 'EMP-000003', 'first_name' => 'Marta', 'last_name' => 'Supervisora', 'position' => 'Supervisor', 'phone' => '5555-0203', 'email' => 'marta.supervisora@example.com', 'user_id' => null],
            ['code' => 'EMP-000004', 'first_name' => 'Diego', 'last_name' => 'Instructor', 'position' => 'Instructor', 'phone' => '5555-0204', 'email' => 'diego.instructor@example.com', 'user_id' => null],
            ['code' => 'EMP-000005', 'first_name' => 'Sofía', 'last_name' => 'Limpieza', 'position' => 'Personal de limpieza', 'phone' => '5555-0205', 'email' => 'sofia.limpieza@example.com', 'user_id' => null],
        ];

        $morningShiftId = WorkShift::query()->where('name', 'Matutino')->value('id');

        foreach ($employees as $employeeData) {
            $positionId = $positionIds[$employeeData['position']] ?? null;

            if ($positionId === null) {
                continue;
            }

            $employee = Employee::query()->updateOrCreate(
                ['code' => $employeeData['code']],
                [
                    'branch_id' => $branch->getKey(),
                    'position_id' => $positionId,
                    'user_id' => $employeeData['user_id'],
                    'first_name' => $employeeData['first_name'],
                    'last_name' => $employeeData['last_name'],
                    'phone' => $employeeData['phone'],
                    'email' => $employeeData['email'],
                    'hired_at' => '2026-09-01',
                    'status' => EmployeeStatus::Active,
                ],
            );

            if ($morningShiftId !== null) {
                EmployeeShiftAssignment::query()->firstOrCreate(
                    [
                        'employee_id' => $employee->getKey(),
                        'work_shift_id' => $morningShiftId,
                        'effective_from' => '2026-09-01',
                        'day_of_week' => null,
                    ],
                    ['is_active' => true],
                );
            }
        }
    }
}

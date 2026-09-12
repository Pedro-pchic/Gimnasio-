<?php

namespace App\Http\Controllers\Web;

use App\EmployeeAttendanceMethod;
use App\EmployeeAttendanceStatus;
use App\EmployeeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEmployeeAttendanceCheckoutRequest;
use App\Http\Requests\Api\V1\StoreEmployeeAttendanceRequest;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeShiftAssignment;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmployeeAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')],
            'attendance_date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::enum(EmployeeAttendanceStatus::class)],
        ]);

        return view('employee-attendances.index', [
            'attendances' => EmployeeAttendance::query()
                ->with(['employee.position', 'branch', 'workShift'])
                ->when($filters['employee_id'] ?? null, fn (Builder $query, int $employeeId): Builder => $query->where('employee_id', $employeeId))
                ->when($filters['branch_id'] ?? null, fn (Builder $query, int $branchId): Builder => $query->where('branch_id', $branchId))
                ->when($filters['attendance_date'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('attendance_date', $date))
                ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
                ->orderByDesc('attendance_date')
                ->orderByDesc('entry_time')
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString(),
            'employees' => Employee::query()->with(['branch', 'position'])->orderBy('last_name')->orderBy('first_name')->orderBy('id')->get(),
            'branches' => Branch::query()->orderBy('name')->orderBy('id')->get(),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $today = today();
        $employees = Employee::query()
            ->active()
            ->with([
                'branch',
                'position',
                'shiftAssignments' => fn (HasMany $query): HasMany => $query
                    ->applicableOn($today)
                    ->with('workShift')
                    ->orderByDesc('effective_from')
                    ->orderByDesc('id'),
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('id')
            ->get();

        return view('employee-attendances.form', [
            'employees' => $employees,
            'selectedEmployeeId' => $request->integer('employee_id') ?: null,
            'attendanceDate' => $today->toDateString(),
        ]);
    }

    public function store(StoreEmployeeAttendanceRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $attendance = Cache::lock("employee-attendance-entry:{$data['employee_id']}", 10)
                ->block(5, function () use ($data): EmployeeAttendance {
                    return DB::transaction(function () use ($data): EmployeeAttendance {
                        $employee = Employee::query()->lockForUpdate()->findOrFail($data['employee_id']);

                        if ($employee->status !== EmployeeStatus::Active) {
                            throw ValidationException::withMessages([
                                'employee_id' => 'El empleado debe estar activo para registrar asistencia.',
                            ]);
                        }

                        if (EmployeeAttendance::query()->where('employee_id', $employee->getKey())->open()->lockForUpdate()->exists()) {
                            throw ValidationException::withMessages([
                                'employee_id' => 'El empleado ya tiene una asistencia laboral abierta.',
                            ]);
                        }

                        $attendanceDate = Carbon::parse($data['attendance_date']);
                        $shiftAssignment = EmployeeShiftAssignment::query()
                            ->applicableOn($attendanceDate)
                            ->with('workShift')
                            ->where('employee_id', $employee->getKey())
                            ->lockForUpdate()
                            ->orderByDesc('effective_from')
                            ->orderByDesc('id')
                            ->first();

                        return EmployeeAttendance::query()->create([
                            'employee_id' => $employee->getKey(),
                            'branch_id' => $employee->branch_id,
                            'work_shift_id' => $shiftAssignment?->work_shift_id,
                            'attendance_date' => $attendanceDate->toDateString(),
                            'entry_time' => $data['entry_time'],
                            'registration_method' => EmployeeAttendanceMethod::Manual,
                            'status' => EmployeeAttendanceStatus::Present,
                            'observations' => $data['observations'] ?? null,
                        ]);
                    });
                });
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages([
                'employee_id' => 'No fue posible registrar la entrada. Intenta de nuevo.',
            ]);
        }

        return redirect()->route('employee-attendances.show', $attendance)->with('success', 'Entrada laboral registrada correctamente.');
    }

    public function show(EmployeeAttendance $employeeAttendance): View
    {
        return view('employee-attendances.show', [
            'attendance' => $employeeAttendance->load(['employee.position', 'branch', 'workShift']),
        ]);
    }

    public function checkOut(StoreEmployeeAttendanceCheckoutRequest $request, EmployeeAttendance $employeeAttendance): RedirectResponse
    {
        $data = $request->validated();

        $attendance = DB::transaction(function () use ($data, $employeeAttendance): EmployeeAttendance {
            $attendance = EmployeeAttendance::query()->lockForUpdate()->findOrFail($employeeAttendance->getKey());

            if ($attendance->exit_time !== null) {
                throw ValidationException::withMessages([
                    'employee_attendance' => 'La asistencia laboral ya tiene una salida registrada.',
                ]);
            }

            $date = $attendance->attendance_date->toDateString();
            $entry = Carbon::parse("{$date} {$attendance->entry_time}");
            $exit = Carbon::parse("{$date} {$data['exit_time']}");

            if ($exit->lessThanOrEqualTo($entry)) {
                $attendance->loadMissing('workShift');

                if (! $attendance->workShift?->spansMidnight()) {
                    throw ValidationException::withMessages([
                        'exit_time' => 'La hora de salida debe ser posterior a la hora de entrada.',
                    ]);
                }
            }

            $attendance->update([
                'exit_time' => $data['exit_time'],
                'status' => EmployeeAttendanceStatus::Finalized,
                'observations' => $data['observations'] ?? $attendance->observations,
            ]);

            return $attendance;
        });

        return redirect()->route('employee-attendances.show', $attendance)->with('success', 'Salida laboral registrada correctamente.');
    }
}

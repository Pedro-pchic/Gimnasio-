<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEmployeeShiftAssignmentRequest;
use App\Http\Requests\Api\V1\UpdateEmployeeShiftAssignmentRequest;
use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\WorkShift;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmployeeShiftAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $selectedEmployeeId = $request->integer('employee_id') ?: null;

        return view('employee-shift-assignments.index', [
            'assignments' => EmployeeShiftAssignment::query()
                ->with(['employee.branch', 'employee.position', 'workShift'])
                ->when($selectedEmployeeId, fn (Builder $query): Builder => $query->where('employee_id', $selectedEmployeeId))
                ->orderByDesc('is_active')
                ->orderByDesc('effective_from')
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString(),
            'employees' => Employee::query()->with(['branch', 'position'])->orderBy('last_name')->orderBy('first_name')->orderBy('id')->get(),
            'selectedEmployeeId' => $selectedEmployeeId,
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StoreEmployeeShiftAssignmentRequest $request): RedirectResponse
    {
        $assignment = $this->persist($request->validated());

        return redirect()->route('employees.show', $assignment->employee_id)->with('success', 'Turno asignado correctamente.');
    }

    public function edit(EmployeeShiftAssignment $employeeShiftAssignment): View
    {
        return $this->formView($employeeShiftAssignment);
    }

    public function update(UpdateEmployeeShiftAssignmentRequest $request, EmployeeShiftAssignment $employeeShiftAssignment): RedirectResponse
    {
        $assignment = $this->persist($request->validated(), $employeeShiftAssignment);

        return redirect()->route('employees.show', $assignment->employee_id)->with('success', 'Asignación actualizada correctamente.');
    }

    public function toggleStatus(EmployeeShiftAssignment $employeeShiftAssignment): RedirectResponse
    {
        $employeeShiftAssignment->update(['is_active' => ! $employeeShiftAssignment->is_active]);

        return back()->with('success', 'Estado de la asignación actualizado correctamente.');
    }

    private function formView(?EmployeeShiftAssignment $employeeShiftAssignment = null): View
    {
        return view('employee-shift-assignments.form', [
            'employeeShiftAssignment' => $employeeShiftAssignment,
            'employees' => Employee::query()->active()->with(['branch', 'position'])->orderBy('last_name')->orderBy('first_name')->orderBy('id')->get(),
            'workShifts' => WorkShift::query()->active()->orderBy('name')->orderBy('id')->get(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function persist(array $data, ?EmployeeShiftAssignment $employeeShiftAssignment = null): EmployeeShiftAssignment
    {
        return DB::transaction(function () use ($data, $employeeShiftAssignment): EmployeeShiftAssignment {
            Employee::query()->lockForUpdate()->findOrFail($data['employee_id']);
            $assignment = $employeeShiftAssignment === null
                ? null
                : EmployeeShiftAssignment::query()->lockForUpdate()->findOrFail($employeeShiftAssignment->getKey());

            if (($data['is_active'] ?? true) && $this->hasConflictingAssignment($data, $assignment)) {
                throw ValidationException::withMessages([
                    'employee_id' => 'El empleado ya tiene un turno activo incompatible durante ese período.',
                ]);
            }

            if ($assignment === null) {
                return EmployeeShiftAssignment::query()->create($data);
            }

            $assignment->update($data);

            return $assignment;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hasConflictingAssignment(array $data, ?EmployeeShiftAssignment $currentAssignment): bool
    {
        $effectiveUntil = $data['effective_until'] ?? '9999-12-31';
        $dayOfWeek = $data['day_of_week'] ?? null;

        return EmployeeShiftAssignment::query()
            ->where('employee_id', $data['employee_id'])
            ->where('is_active', true)
            ->when($currentAssignment, fn (Builder $query): Builder => $query->whereKeyNot($currentAssignment->getKey()))
            ->whereDate('effective_from', '<=', $effectiveUntil)
            ->where(function (Builder $query) use ($data): void {
                $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', $data['effective_from']);
            })
            ->where(function (Builder $query) use ($dayOfWeek): void {
                if ($dayOfWeek === null) {
                    $query->whereNotNull('day_of_week')->orWhereNull('day_of_week');

                    return;
                }

                $query->whereNull('day_of_week')->orWhere('day_of_week', $dayOfWeek);
            })
            ->lockForUpdate()
            ->exists();
    }
}

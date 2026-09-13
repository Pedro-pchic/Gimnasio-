<?php

namespace App\Http\Controllers\Web;

use App\EmployeeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEmployeeRequest;
use App\Http\Requests\Api\V1\UpdateEmployeeRequest;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $selectedBranchId = $request->integer('branch_id') ?: null;
        $selectedStatus = EmployeeStatus::tryFrom($request->string('status')->toString());

        return view('employees.index', [
            'employees' => Employee::query()
                ->with(['branch', 'position', 'user'])
                ->when($selectedBranchId, fn (Builder $query): Builder => $query->where('branch_id', $selectedBranchId))
                ->when($selectedStatus, fn (Builder $query): Builder => $query->where('status', $selectedStatus))
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->orderBy('id')
                ->paginate(15)
                ->withQueryString(),
            'branches' => Branch::query()->orderBy('name')->orderBy('id')->get(),
            'selectedBranchId' => $selectedBranchId,
            'selectedStatus' => $selectedStatus?->value,
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = Employee::query()->create($request->validated());

        return redirect()->route('employees.show', $employee)->with('success', 'Empleado creado correctamente.');
    }

    public function show(Employee $employee): View
    {
        $today = today();

        return view('employees.show', [
            'employee' => $employee->load([
                'branch',
                'position',
                'user',
                'shiftAssignments' => fn (HasMany $query): HasMany => $query
                    ->with('workShift')
                    ->orderByDesc('is_active')
                    ->orderByDesc('effective_from')
                    ->orderByDesc('id'),
                'attendances' => fn (HasMany $query): HasMany => $query
                    ->with(['branch', 'workShift'])
                    ->orderByDesc('attendance_date')
                    ->orderByDesc('entry_time')
                    ->orderByDesc('id')
                    ->limit(25),
                'gymClasses' => fn (HasMany $query): HasMany => $query
                    ->with('branch')
                    ->orderBy('name')
                    ->orderBy('id'),
            ]),
            'currentShiftAssignment' => $employee->shiftAssignments()
                ->applicableOn($today)
                ->with('workShift')
                ->orderByDesc('effective_from')
                ->orderByDesc('id')
                ->first(),
        ]);
    }

    public function edit(Employee $employee): View
    {
        return $this->formView($employee);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        return redirect()->route('employees.show', $employee)->with('success', 'Empleado actualizado correctamente.');
    }

    public function toggleStatus(Employee $employee): RedirectResponse
    {
        $employee->update([
            'status' => $employee->status === EmployeeStatus::Active
                ? EmployeeStatus::Inactive
                : EmployeeStatus::Active,
        ]);

        return back()->with('success', 'Estado del empleado actualizado correctamente.');
    }

    private function formView(?Employee $employee = null): View
    {
        return view('employees.form', [
            'employee' => $employee,
            'branches' => Branch::query()->orderBy('name')->orderBy('id')->get(),
            'positions' => Position::query()->orderByDesc('is_active')->orderBy('name')->orderBy('id')->get(),
            'users' => User::query()
                ->where(function (Builder $query) use ($employee): void {
                    $query->whereDoesntHave('employee');

                    if ($employee?->user_id !== null) {
                        $query->orWhereKey($employee->user_id);
                    }
                })
                ->orderBy('name')
                ->orderBy('id')
                ->get(),
        ]);
    }
}

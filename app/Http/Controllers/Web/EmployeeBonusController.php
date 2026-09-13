<?php

namespace App\Http\Controllers\Web;

use App\EmployeeBonusStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEmployeeBonusRequest;
use App\Models\Employee;
use App\Models\EmployeeBonus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmployeeBonusController extends Controller
{
    public function index(): View
    {
        return view('employee-bonuses.index', ['bonuses' => EmployeeBonus::query()->with(['employee', 'approver'])->latest('id')->paginate(15)]);
    }

    public function create(): View
    {
        return view('employee-bonuses.form', ['employees' => Employee::query()->where('status', 'active')->orderBy('first_name')->get()]);
    }

    public function store(StoreEmployeeBonusRequest $request): RedirectResponse
    {
        EmployeeBonus::query()->create([...$request->validated(), 'status' => EmployeeBonusStatus::Pending]);

        return redirect()->route('employee-bonuses.index')->with('success', 'Bono registrado correctamente.');
    }

    public function approve(EmployeeBonus $employeeBonus): RedirectResponse
    {
        DB::transaction(function () use ($employeeBonus): void {
            $bonus = EmployeeBonus::query()->lockForUpdate()->findOrFail($employeeBonus->id);
            if ($bonus->status !== EmployeeBonusStatus::Pending) {
                throw ValidationException::withMessages(['bonus' => 'Solo se pueden aprobar bonos pendientes.']);
            }
            $bonus->update(['status' => EmployeeBonusStatus::Approved, 'approved_by' => auth()->id()]);
        });

        return back()->with('success', 'Bono aprobado correctamente.');
    }

    public function cancel(EmployeeBonus $employeeBonus): RedirectResponse
    {
        $employeeBonus->update(['status' => EmployeeBonusStatus::Cancelled]);

        return back()->with('success', 'Bono cancelado correctamente.');
    }
}

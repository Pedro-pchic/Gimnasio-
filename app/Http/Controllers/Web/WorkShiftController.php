<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWorkShiftRequest;
use App\Http\Requests\Api\V1\UpdateWorkShiftRequest;
use App\Models\WorkShift;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkShiftController extends Controller
{
    public function index(): View
    {
        return view('work-shifts.index', [
            'workShifts' => WorkShift::query()
                ->withCount('employeeShiftAssignments')
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('work-shifts.form', ['workShift' => null]);
    }

    public function store(StoreWorkShiftRequest $request): RedirectResponse
    {
        WorkShift::query()->create($request->validated());

        return redirect()->route('work-shifts.index')->with('success', 'Turno creado correctamente.');
    }

    public function edit(WorkShift $workShift): View
    {
        return view('work-shifts.form', ['workShift' => $workShift]);
    }

    public function update(UpdateWorkShiftRequest $request, WorkShift $workShift): RedirectResponse
    {
        $workShift->update($request->validated());

        return redirect()->route('work-shifts.index')->with('success', 'Turno actualizado correctamente.');
    }

    public function toggleStatus(WorkShift $workShift): RedirectResponse
    {
        $workShift->update(['is_active' => ! $workShift->is_active]);

        return back()->with('success', 'Estado del turno actualizado correctamente.');
    }
}

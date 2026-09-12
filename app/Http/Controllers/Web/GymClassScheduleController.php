<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreGymClassScheduleRequest;
use App\Http\Requests\Api\V1\UpdateGymClassScheduleRequest;
use App\Models\Branch;
use App\Models\GymClass;
use App\Models\GymClassSchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GymClassScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $selectedBranchId = $request->integer('branch_id') ?: null;

        return view('class-schedules.index', [
            'schedules' => GymClassSchedule::query()
                ->with('gymClass.branch')
                ->when(
                    $selectedBranchId,
                    fn (Builder $query): Builder => $query->whereHas(
                        'gymClass',
                        fn (Builder $query): Builder => $query->where('branch_id', $selectedBranchId),
                    ),
                )
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->paginate(15)
                ->withQueryString(),
            'branches' => Branch::query()->orderBy('name')->orderBy('id')->get(),
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return $this->formView();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGymClassScheduleRequest $request): RedirectResponse
    {
        $schedule = GymClassSchedule::query()->create($request->validated());

        return redirect()->route('gym-classes.show', $schedule->gym_class_id)->with('success', 'Horario creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(GymClassSchedule $classSchedule): View
    {
        return $this->formView($classSchedule);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGymClassScheduleRequest $request, GymClassSchedule $classSchedule): RedirectResponse
    {
        $classSchedule->update($request->validated());

        return redirect()->route('gym-classes.show', $classSchedule->gym_class_id)->with('success', 'Horario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    private function formView(?GymClassSchedule $classSchedule = null): View
    {
        return view('class-schedules.form', [
            'classSchedule' => $classSchedule,
            'gymClasses' => GymClass::query()->with('branch')->orderBy('name')->orderBy('id')->get(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\GymClassEnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreGymClassRequest;
use App\Http\Requests\Api\V1\UpdateGymClassRequest;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\GymClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GymClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('gym-classes.index', [
            'gymClasses' => GymClass::query()
                ->with(['branch', 'instructor.position'])
                ->withCount('schedules')
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(15),
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
    public function store(StoreGymClassRequest $request): RedirectResponse
    {
        $gymClass = GymClass::query()->create($request->validated());

        return redirect()->route('gym-classes.show', $gymClass)->with('success', 'Clase creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GymClass $gymClass): View
    {
        $today = now()->toDateString();

        return view('gym-classes.show', [
            'gymClass' => $gymClass->load([
                'branch',
                'instructor.position',
                'schedules' => fn (HasMany $query): HasMany => $query
                    ->withCount([
                        'enrollments as enrolled_today_count' => fn (Builder $query): Builder => $query
                            ->whereDate('enrollment_date', $today)
                            ->whereIn('status', GymClassEnrollmentStatus::capacityBlockingValues()),
                    ])
                    ->orderBy('day_of_week')
                    ->orderBy('start_time'),
                'enrollments' => fn (HasManyThrough $query): HasManyThrough => $query
                    ->with(['client', 'gymClassSchedule'])
                    ->orderByDesc('enrollment_date')
                    ->orderByDesc('id')
                    ->limit(25),
            ]),
            'today' => $today,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GymClass $gymClass): View
    {
        return $this->formView($gymClass);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGymClassRequest $request, GymClass $gymClass): RedirectResponse
    {
        $gymClass->update($request->validated());

        return redirect()->route('gym-classes.show', $gymClass)->with('success', 'Clase actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function toggleStatus(GymClass $gymClass): RedirectResponse
    {
        $gymClass->update(['is_active' => ! $gymClass->is_active]);

        return back()->with('success', 'Estado de la clase actualizado correctamente.');
    }

    private function formView(?GymClass $gymClass = null): View
    {
        return view('gym-classes.form', [
            'gymClass' => $gymClass,
            'branches' => Branch::query()->with('services')->orderBy('name')->orderBy('id')->get(),
            'instructors' => Employee::query()
                ->active()
                ->whereHas('position', fn (Builder $query): Builder => $query->where('is_active', true)->where('can_teach', true))
                ->with(['branch', 'position'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->orderBy('id')
                ->get(),
        ]);
    }
}

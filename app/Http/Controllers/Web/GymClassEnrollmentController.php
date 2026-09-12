<?php

namespace App\Http\Controllers\Web;

use App\ClientMembershipStatus;
use App\GymClassEnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreGymClassEnrollmentRequest;
use App\Models\Client;
use App\Models\GymClass;
use App\Models\GymClassEnrollment;
use App\Models\GymClassSchedule;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GymClassEnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('class-enrollments.index', [
            'enrollments' => GymClassEnrollment::query()
                ->with(['client.branch', 'gymClassSchedule.gymClass.branch'])
                ->orderByDesc('enrollment_date')
                ->orderByDesc('id')
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
    public function store(StoreGymClassEnrollmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $enrollment = DB::transaction(function () use ($data): GymClassEnrollment {
            $client = Client::query()->lockForUpdate()->findOrFail($data['client_id']);

            if (! $client->is_active) {
                throw ValidationException::withMessages(['client_id' => 'El cliente debe estar activo para inscribirse.']);
            }

            $schedule = GymClassSchedule::query()->lockForUpdate()->findOrFail($data['gym_class_schedule_id']);
            $gymClass = GymClass::query()->lockForUpdate()->findOrFail($schedule->gym_class_id);

            if (! $gymClass->is_active || ! $schedule->is_active) {
                throw ValidationException::withMessages(['gym_class_schedule_id' => 'La actividad y su horario deben estar activos.']);
            }

            $classDate = Carbon::parse($data['enrollment_date']);

            if (strtolower($classDate->englishDayOfWeek) !== $schedule->day_of_week) {
                throw ValidationException::withMessages(['enrollment_date' => 'La fecha debe coincidir con el día del horario seleccionado.']);
            }

            $hasValidMembership = $client->memberships()
                ->where('status', ClientMembershipStatus::Active)
                ->whereDate('start_date', '<=', $classDate)
                ->whereDate('end_date', '>=', $classDate)
                ->exists();

            if (! $hasValidMembership) {
                throw ValidationException::withMessages(['client_id' => 'El cliente no tiene una membresía activa para esta fecha.']);
            }

            $alreadyEnrolled = $schedule->enrollments()
                ->where('client_id', $client->getKey())
                ->whereDate('enrollment_date', $classDate)
                ->exists();

            if ($alreadyEnrolled) {
                throw ValidationException::withMessages(['client_id' => 'El cliente ya tiene una inscripción para este horario y fecha.']);
            }

            $enrolledCount = $schedule->enrollments()
                ->capacityBlocking()
                ->whereDate('enrollment_date', $classDate)
                ->count();

            if ($enrolledCount >= $gymClass->maximum_capacity) {
                throw ValidationException::withMessages(['gym_class_schedule_id' => 'No hay cupos disponibles para este horario.']);
            }

            return GymClassEnrollment::query()->create([
                'client_id' => $client->getKey(),
                'gym_class_schedule_id' => $schedule->getKey(),
                'enrollment_date' => $classDate->toDateString(),
                'status' => GymClassEnrollmentStatus::Enrolled,
            ]);
        });

        return redirect()->route('class-enrollments.show', $enrollment)->with('success', 'Cliente inscrito correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GymClassEnrollment $classEnrollment): View
    {
        return view('class-enrollments.show', [
            'enrollment' => $classEnrollment->load(['client.branch', 'gymClassSchedule.gymClass.branch']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function cancel(GymClassEnrollment $classEnrollment): RedirectResponse
    {
        $classEnrollment->update(['status' => GymClassEnrollmentStatus::Cancelled]);

        return redirect()->route('class-enrollments.show', $classEnrollment)->with('success', 'Inscripción cancelada correctamente.');
    }

    public function markAttendance(GymClassEnrollment $classEnrollment): RedirectResponse
    {
        if ($classEnrollment->status === GymClassEnrollmentStatus::Cancelled) {
            throw ValidationException::withMessages([
                'class_enrollment' => 'No se puede marcar asistencia en una inscripción cancelada.',
            ]);
        }

        $classEnrollment->update(['status' => GymClassEnrollmentStatus::Attended]);

        return redirect()->route('class-enrollments.show', $classEnrollment)->with('success', 'Asistencia marcada correctamente.');
    }

    private function formView(): View
    {
        return view('class-enrollments.form', [
            'clients' => Client::query()->where('is_active', true)->orderBy('last_name')->orderBy('first_name')->orderBy('id')->get(),
            'schedules' => GymClassSchedule::query()
                ->with('gymClass.branch')
                ->where('is_active', true)
                ->whereHas('gymClass', fn ($query) => $query->where('is_active', true))
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get(),
        ]);
    }
}

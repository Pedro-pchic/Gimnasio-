@extends('layouts.admin')

@section('title', 'Reservas')

@section('content')
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-600">Operaciones</p>
            <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Reservas y control de cupo</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-500 sm:text-base">Consulta la disponibilidad por actividad antes de confirmar una reserva.</p>
        </div>
        @can('enrollments.register')
            <a href="{{ route('class-enrollments.create', ['enrollment_date' => $reservationDate->toDateString()]) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-500">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                Nueva reserva
            </a>
        @endcan
    </div>

    <form method="GET" class="mb-5 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
        <div class="sm:w-64">
            <label for="date" class="text-sm font-semibold text-slate-700">Fecha de reserva</label>
            <input id="date" name="date" type="date" value="{{ $reservationDate->toDateString() }}" class="mt-1.5 block w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-blue-500 focus:bg-white focus:ring-blue-500">
        </div>
        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">Consultar cupos</button>
    </form>

    <section>
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-600">{{ $reservationDate->format('d/m/Y') }}</p>
                <h3 class="mt-1 text-xl font-bold tracking-tight text-slate-950">Disponibilidad por horario</h3>
            </div>
            <span class="rounded-full bg-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600">{{ $schedules->count() }} horarios activos</span>
        </div>

        <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
            @forelse ($schedules as $schedule)
                @php
                    $availableSeats = max(0, $schedule->maximum_capacity - $schedule->reserved_count);
                    $occupancyPercentage = (int) round(($schedule->reserved_count / max(1, $schedule->maximum_capacity)) * 100);
                    $isFull = $availableSeats === 0;
                    $hasFewSeats = ! $isFull && $availableSeats <= max(2, (int) ceil($schedule->maximum_capacity * 0.25));
                @endphp

                <article @class([
                    'overflow-hidden rounded-2xl border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md',
                    'border-red-200' => $isFull,
                    'border-amber-200' => $hasFewSeats,
                    'border-slate-200' => ! $isFull && ! $hasFewSeats,
                ])>
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="truncate text-lg font-bold tracking-tight text-slate-950">{{ $schedule->gymClass->name }}</h4>
                                @if ($schedule->gymClass->requires_premium)
                                    <span class="rounded-full bg-violet-100 px-2 py-1 text-[11px] font-bold uppercase tracking-wide text-violet-700">Premium</span>
                                @endif
                            </div>
                            <p class="mt-2 flex items-center gap-2 text-sm font-medium text-slate-500">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4" aria-hidden="true"><circle cx="12" cy="12" r="8" /><path d="M12 8v4l3 2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                {{ substr($schedule->start_time, 0, 5) }} – {{ substr($schedule->end_time, 0, 5) }}
                            </p>
                        </div>
                        @if ($isFull)
                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">Completo</span>
                        @elseif ($hasFewSeats)
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Pocos cupos</span>
                        @else
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">Disponible</span>
                        @endif
                    </div>

                    <div class="mt-6 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-3xl font-bold tracking-tight text-slate-950">{{ $schedule->reserved_count }} <span class="text-lg font-semibold text-slate-400">/ {{ $schedule->maximum_capacity }}</span></p>
                            <p class="mt-1 text-sm font-medium text-slate-500">personas reservadas</p>
                        </div>
                        <p @class([
                            'text-sm font-bold',
                            'text-red-600' => $isFull,
                            'text-amber-600' => $hasFewSeats,
                            'text-emerald-600' => ! $isFull && ! $hasFewSeats,
                        ])>{{ $isFull ? 'Sin cupos' : $availableSeats.' cupos disponibles' }}</p>
                    </div>

                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                        <span @class([
                            'block h-full rounded-full transition-all',
                            'bg-red-500' => $isFull,
                            'bg-amber-500' => $hasFewSeats,
                            'bg-gradient-to-r from-blue-500 to-indigo-500' => ! $isFull && ! $hasFewSeats,
                        ]) style="width: {{ $occupancyPercentage }}%"></span>
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-4 border-t border-slate-100 pt-4">
                        <span class="text-xs font-medium text-slate-400">Ocupación {{ $occupancyPercentage }}%</span>
                        @can('enrollments.register')
                            @if (! $isFull)
                                <a href="{{ route('class-enrollments.create', ['gym_class_schedule_id' => $schedule->id, 'enrollment_date' => $reservationDate->toDateString()]) }}" class="text-sm font-bold text-blue-700 transition hover:text-blue-500">Reservar →</a>
                            @endif
                        @endcan
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500 md:col-span-2 2xl:col-span-3">No hay horarios activos para esta fecha.</div>
            @endforelse
        </div>
    </section>

    <section class="mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-600">Agenda del día</p>
                <h3 class="mt-1 text-xl font-bold tracking-tight text-slate-950">Reservas confirmadas</h3>
            </div>
            <span class="text-sm font-medium text-slate-500">{{ $reservations->count() }} registros</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50/80 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Cliente</th>
                        <th class="px-5 py-3.5">Actividad</th>
                        <th class="px-5 py-3.5">Horario</th>
                        <th class="px-5 py-3.5">Estado</th>
                        <th class="px-5 py-3.5 text-right"><span class="sr-only">Acción</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reservations as $reservation)
                        <tr class="transition hover:bg-blue-50/50">
                            <td class="px-5 py-4 font-semibold text-slate-800">{{ $reservation->client->first_name }} {{ $reservation->client->last_name }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $reservation->gymClassSchedule->gymClass->name }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ substr($reservation->gymClassSchedule->start_time, 0, 5) }} – {{ substr($reservation->gymClassSchedule->end_time, 0, 5) }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700">{{ $reservation->status->label() }}</span></td>
                            <td class="px-5 py-4 text-right">
                                @can('enrollments.register')
                                    <form method="POST" action="{{ route('class-enrollments.cancel', $reservation) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="rounded-lg px-2.5 py-1.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 hover:text-red-700">Cancelar</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">No hay reservas confirmadas para esta fecha.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

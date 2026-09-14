@extends('layouts.admin')

@section('title', $classSchedule ? 'Editar horario' : 'Nuevo horario')

@section('content')
    <div class="mb-6">
        <a href="{{ route('class-schedules.index') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-500">← Volver a horarios</a>
        <h1 class="mt-3 text-3xl font-bold tracking-tight">{{ $classSchedule ? 'Editar horario' : 'Nuevo horario' }}</h1>
        <p class="mt-2 text-slate-600">Los horarios son semanales y recurrentes; las reservas indican la fecha específica de participación.</p>
    </div>

    <form method="POST" action="{{ $classSchedule ? route('class-schedules.update', $classSchedule) : route('class-schedules.store') }}" class="max-w-4xl rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-7">
        @csrf
        @if ($classSchedule)
            @method('PUT')
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="gym_class_id" class="text-sm font-medium text-slate-700">Clase o actividad</label>
                <select id="gym_class_id" name="gym_class_id" required class="mt-1 block w-full rounded-lg border-slate-300">
                    <option value="">Selecciona una actividad</option>
                    @foreach ($gymClasses as $gymClass)
                        <option value="{{ $gymClass->id }}" @selected((int) old('gym_class_id', $classSchedule?->gym_class_id ?? request('gym_class_id')) === $gymClass->id)>{{ $gymClass->name }} · {{ $gymClass->branch->name }}</option>
                    @endforeach
                </select>
                <x-field-error name="gym_class_id" />
            </div>

            <div>
                <label for="day_of_week" class="text-sm font-medium text-slate-700">Día de semana</label>
                <select id="day_of_week" name="day_of_week" required class="mt-1 block w-full rounded-lg border-slate-300">
                    @foreach (['monday' => 'Lunes', 'tuesday' => 'Martes', 'wednesday' => 'Miércoles', 'thursday' => 'Jueves', 'friday' => 'Viernes', 'saturday' => 'Sábado', 'sunday' => 'Domingo'] as $day => $label)
                        <option value="{{ $day }}" @selected(old('day_of_week', $classSchedule?->day_of_week ?? 'monday') === $day)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-field-error name="day_of_week" />
            </div>

            <div>
                <label for="maximum_capacity" class="text-sm font-medium text-slate-700">Capacidad máxima</label>
                <input id="maximum_capacity" name="maximum_capacity" type="number" min="1" value="{{ old('maximum_capacity', $classSchedule?->maximum_capacity ?? 20) }}" required class="mt-1 block w-full rounded-lg border-slate-300">
                <x-field-error name="maximum_capacity" />
            </div>

            <div>
                <label for="start_time" class="text-sm font-medium text-slate-700">Hora de inicio</label>
                <input id="start_time" name="start_time" type="time" value="{{ old('start_time', $classSchedule ? substr($classSchedule->start_time, 0, 5) : '') }}" required class="mt-1 block w-full rounded-lg border-slate-300">
                <x-field-error name="start_time" />
            </div>

            <div>
                <label for="end_time" class="text-sm font-medium text-slate-700">Hora de fin</label>
                <input id="end_time" name="end_time" type="time" value="{{ old('end_time', $classSchedule ? substr($classSchedule->end_time, 0, 5) : '') }}" required class="mt-1 block w-full rounded-lg border-slate-300">
                <x-field-error name="end_time" />
            </div>

            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 sm:col-span-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $classSchedule?->is_active ?? true)) class="rounded border-slate-300 text-indigo-600">
                Horario activo
            </label>
        </div>

        <div class="mt-7 flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ $classSchedule ? 'Guardar cambios' : 'Crear horario' }}</button>
            <a href="{{ $classSchedule ? route('gym-classes.show', $classSchedule->gym_class_id) : route('class-schedules.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
        </div>
    </form>
@endsection

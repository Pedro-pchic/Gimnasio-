@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $statisticsByLabel = collect($statistics)->keyBy('label');
        $valueFor = fn (string $label): mixed => data_get($statisticsByLabel->get($label), 'value', 0);
        $primaryLabels = [
            'Clientes activos',
            'Membresías activas',
            'Participantes inscritos hoy',
            'Pagos cobrados hoy',
        ];
    @endphp

    <div class="mb-7 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-600">Resumen operativo</p>
            <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">El gimnasio, de un vistazo</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-500 sm:text-base">Indicadores de clientes, operaciones, cupos y actividad comercial para hoy.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            @can('clients.manage')
                <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-500">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                    Nuevo cliente
                </a>
            @endcan
            @can('payments.manage')
                <a href="{{ route('payments.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="M3 10h18" /></svg>
                    Registrar pago
                </a>
            @endcan
            @can('enrollments.register')
                <a href="{{ route('class-enrollments.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 10h18M12 14v4m-2-2h4" stroke-linecap="round" /></svg>
                    Nueva reserva
                </a>
            @endcan
            @can('biometric-access.view')
                <a href="{{ route('biometric-access.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3M9 12a3 3 0 0 1 6 0v2a3 3 0 0 1-6 0v-2Z" stroke-linecap="round" /></svg>
                    Control biométrico
                </a>
            @endcan
        </div>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-4">
        <article class="relative overflow-hidden rounded-2xl bg-slate-950 p-5 text-white shadow-lg shadow-slate-300 sm:p-6">
            <div class="absolute -right-5 -top-5 size-28 rounded-full bg-blue-500/20"></div>
            <div class="relative flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-300">Clientes activos</p>
                    <p class="mt-3 text-4xl font-bold tracking-tight">{{ $valueFor('Clientes activos') }}</p>
                    <p class="mt-3 text-xs font-medium text-slate-400">{{ $valueFor('Sucursales activas') }} sucursales en operación</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-blue-500/20 text-blue-200"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5" aria-hidden="true"><path d="M16 20v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM17 11a3 3 0 1 0 0-6" stroke-linecap="round" /></svg></span>
            </div>
        </article>

        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Membresías activas</p>
                    <p class="mt-3 text-4xl font-bold tracking-tight text-slate-950">{{ $valueFor('Membresías activas') }}</p>
                    <p class="mt-3 text-xs font-medium text-emerald-700">{{ $valueFor('Renovaciones recientes') }} renovaciones recientes</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="M3 10h18M7 15h3" stroke-linecap="round" /></svg></span>
            </div>
        </article>

        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Accesos y reservas</p>
                    <p class="mt-3 text-4xl font-bold tracking-tight text-slate-950">{{ $valueFor('Participantes inscritos hoy') }}</p>
                    <p class="mt-2 text-xs font-semibold text-slate-600">Participantes inscritos hoy</p>
                    <p class="mt-1 text-xs font-medium text-blue-700">{{ $valueFor('Cupos disponibles hoy') }} cupos disponibles hoy</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3M9 12a3 3 0 0 1 6 0v2a3 3 0 0 1-6 0v-2Z" stroke-linecap="round" /></svg></span>
            </div>
        </article>

        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Pagos cobrados hoy</p>
                    <p class="mt-3 text-4xl font-bold tracking-tight text-slate-950">{{ $valueFor('Pagos cobrados hoy') }}</p>
                    <p class="mt-3 text-xs font-medium text-violet-700">{{ $valueFor('Ventas completadas hoy') }} ventas completadas</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5" aria-hidden="true"><path d="M4 19V5M4 19h16M8 16v-4M12 16V8M16 16v-7" stroke-linecap="round" /></svg></span>
            </div>
        </article>
    </section>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1.45fr_0.85fr]">
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-600">Clases y reservas</p>
                    <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-950">Capacidad de hoy</h2>
                </div>
                @can('enrollments.view')<a href="{{ route('class-reservations.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-500">Ver reservas</a>@endcan
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-blue-50 p-4">
                    <p class="text-sm font-medium text-blue-800">Clases programadas</p>
                    <p class="mt-2 text-3xl font-bold text-blue-950">{{ $valueFor('Clases de hoy') }}</p>
                    <p class="mt-2 text-xs font-medium text-blue-700">Horario activo del día</p>
                </div>
                <div class="rounded-xl bg-emerald-50 p-4">
                    <p class="text-sm font-medium text-emerald-800">Cupos disponibles</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-950">{{ $valueFor('Cupos disponibles hoy') }}</p>
                    <p class="mt-2 text-xs font-medium text-emerald-700">Disponibilidad total</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-4">
                    <p class="text-sm font-medium text-amber-800">Clases llenas</p>
                    <p class="mt-2 text-3xl font-bold text-amber-950">{{ $valueFor('Clases llenas') }}</p>
                    <p class="mt-2 text-xs font-medium text-amber-700">Requieren atención</p>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex items-center justify-between gap-4 text-sm">
                    <span class="font-semibold text-slate-700">Ocupación registrada</span>
                    <span class="font-bold text-slate-950">{{ $valueFor('Participantes inscritos hoy') }} participantes</span>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200">
                    <span class="block h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500" style="width: {{ $valueFor('Clases llenas') > 0 ? '78' : '42' }}%"></span>
                </div>
            </div>
        </section>

        <aside class="rounded-2xl bg-slate-950 p-5 text-white shadow-lg shadow-slate-300 sm:p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-300">Alertas</p>
            <h2 class="mt-1 text-xl font-bold tracking-tight">Pendientes operativos</h2>
            <div class="mt-5 space-y-3">
                <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 p-3">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-amber-400/15 text-amber-300"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" aria-hidden="true"><path d="M12 8v4m0 4h.01M10.3 4.3 2.9 17.1A2 2 0 0 0 4.6 20h14.8a2 2 0 0 0 1.7-2.9L13.7 4.3a2 2 0 0 0-3.4 0Z" stroke-linecap="round" /></svg></span>
                    <div><p class="text-sm font-semibold">Stock bajo</p><p class="text-xs text-slate-400">{{ $valueFor('Artículos con stock bajo') }} artículos requieren revisión</p></div>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 p-3">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-red-400/15 text-red-300"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" aria-hidden="true"><path d="M12 8v4m0 4h.01M10.3 4.3 2.9 17.1A2 2 0 0 0 4.6 20h14.8a2 2 0 0 0 1.7-2.9L13.7 4.3a2 2 0 0 0-3.4 0Z" stroke-linecap="round" /></svg></span>
                    <div><p class="text-sm font-semibold">Mantenimiento</p><p class="text-xs text-slate-400">{{ $valueFor('Mantenimientos pendientes') }} pendientes · {{ $valueFor('Equipos en mantenimiento') }} equipos</p></div>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 p-3">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-blue-400/15 text-blue-300"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" aria-hidden="true"><path d="M12 8v4m0 4h.01M10.3 4.3 2.9 17.1A2 2 0 0 0 4.6 20h14.8a2 2 0 0 0 1.7-2.9L13.7 4.3a2 2 0 0 0-3.4 0Z" stroke-linecap="round" /></svg></span>
                    <div><p class="text-sm font-semibold">Membresías y clases</p><p class="text-xs text-slate-400">{{ $valueFor('Membresías vencidas') }} vencidas · {{ $valueFor('Clases llenas') }} clases completas</p></div>
                </div>
            </div>
        </aside>
    </div>

    <section class="mt-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-600">Actividad reciente</p>
                <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-950">Resumen operacional completo</h2>
            </div>
            <p class="text-sm text-slate-500">Indicadores actualmente registrados en el sistema.</p>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
            @foreach ($statistics as $statistic)
                @if (! in_array($statistic['label'], $primaryLabels, true))
                    <article class="flex items-center justify-between gap-4 rounded-xl border border-slate-100 bg-slate-50/70 p-4 transition hover:border-blue-100 hover:bg-blue-50/50">
                        <p class="text-sm font-medium text-slate-600">{{ $statistic['label'] }}</p>
                        <p class="shrink-0 text-lg font-bold tracking-tight text-slate-900">{{ $statistic['value'] }}</p>
                    </article>
                @endif
            @endforeach
        </div>
    </section>
@endsection

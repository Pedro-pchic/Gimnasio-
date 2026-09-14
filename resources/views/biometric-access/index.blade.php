@extends('layouts.admin')

@section('title', 'Control biométrico')

@section('content')
    <div class="mb-7">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-600">Operaciones</p>
        <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Terminal de acceso</h2>
        <p class="mt-2 max-w-2xl text-sm text-slate-500 sm:text-base">Simulación visual de lectura de huella para el control de entradas y salidas.</p>
    </div>

    <div class="grid max-w-6xl gap-5 xl:grid-cols-[1.1fr_0.9fr]">
        <section class="relative overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-xl shadow-slate-300 sm:p-8">
            <div class="absolute -right-16 -top-16 size-64 rounded-full bg-blue-500/15 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-16 size-64 rounded-full bg-indigo-500/10 blur-3xl"></div>

            <div class="relative flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="flex size-14 items-center justify-center rounded-2xl bg-blue-500/15 text-blue-200 ring-1 ring-blue-300/20">
                        <svg viewBox="0 0 64 64" class="size-10 fill-none stroke-current" stroke-width="3" stroke-linecap="round" aria-hidden="true">
                            <path d="M23 22c0-5 4-9 9-9s9 4 9 9" />
                            <path d="M17 29c0-9 7-16 15-16s15 7 15 16v5" />
                            <path d="M11 32c0-12 9-22 21-22s21 10 21 22v8" />
                            <path d="M23 30v5c0 5 4 9 9 9s9-4 9-9v-5" />
                            <path d="M17 39v3c0 8 7 15 15 15s15-7 15-15v-3" />
                            <path d="M29 24v12c0 2 1 3 3 3s3-1 3-3V24" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300">Terminal 01</p>
                        <h3 class="mt-1 text-xl font-bold tracking-tight">CONTROL DE ACCESO BIOMÉTRICO</h3>
                    </div>
                </div>
                <span id="terminal-status" class="rounded-full border border-blue-300/20 bg-blue-400/10 px-3 py-1.5 text-xs font-bold text-blue-200">Esperando lectura</span>
            </div>

            @if ($selectedClient)
                <div class="relative mt-8 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Cliente seleccionado</p>
                        <div class="mt-4 flex items-center gap-3">
                            <span class="flex size-12 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 text-lg font-bold text-white">{{ mb_strtoupper(mb_substr($selectedClient->first_name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-lg font-bold">{{ $selectedClient->first_name }} {{ $selectedClient->last_name }}</p>
                                <p class="mt-1 text-sm text-slate-400">{{ $selectedClient->code }}</p>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-white/10 pt-5">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Membresía</p>
                            @if ($activeMembership)
                                <p class="mt-2 flex items-center gap-2 text-sm font-bold text-emerald-300"><span class="size-2 rounded-full bg-emerald-400"></span> Activa</p>
                                <p class="mt-1 text-sm text-slate-300">{{ $activeMembership->membershipType->name }} · vigente hasta {{ $activeMembership->end_date->format('d/m/Y') }}</p>
                            @else
                                <p class="mt-2 flex items-center gap-2 text-sm font-bold text-red-300"><span class="size-2 rounded-full bg-red-400"></span> Vencida / inactiva</p>
                                <p class="mt-1 text-sm text-slate-400">No se registrará ningún acceso.</p>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label for="client_id" class="text-sm font-semibold text-slate-200">Cambiar cliente</label>
                        <select id="client_id" name="client_id" form="biometric-access-form" class="mt-2 block w-full rounded-xl border border-white/10 bg-white px-3 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none ring-0 transition focus:border-blue-300 focus:ring-2 focus:ring-blue-300">
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected($client->is($selectedClient))>{{ $client->first_name }} {{ $client->last_name }}</option>
                            @endforeach
                        </select>

                        <div class="mt-5 rounded-2xl border border-white/10 bg-slate-900/70 p-4 text-center">
                            <span class="mx-auto flex size-20 items-center justify-center rounded-full border border-blue-300/20 bg-blue-500/10 text-blue-200">
                                <svg viewBox="0 0 64 64" class="size-12 fill-none stroke-current" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M23 22c0-5 4-9 9-9s9 4 9 9" /><path d="M17 29c0-9 7-16 15-16s15 7 15 16v5" /><path d="M11 32c0-12 9-22 21-22s21 10 21 22v8" /><path d="M23 30v5c0 5 4 9 9 9s9-4 9-9v-5" /><path d="M17 39v3c0 8 7 15 15 15s15-7 15-15v-3" /></svg>
                            </span>
                            <p id="fingerprint-feedback" class="mt-3 min-h-5 text-sm font-semibold text-blue-200" aria-live="polite">Coloca la huella para iniciar la lectura.</p>
                        </div>

                        <form id="biometric-access-form" method="POST" action="{{ route('biometric-access.store') }}" class="mt-4">
                            @csrf
                            <button id="simulate-fingerprint" type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-500 px-4 py-3.5 text-sm font-bold tracking-wide text-white shadow-lg shadow-blue-950/30 transition hover:bg-blue-400 disabled:cursor-wait disabled:bg-blue-300">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5" aria-hidden="true"><path d="M9 4.5a3.5 3.5 0 0 1 7 0M6 8a6 6 0 0 1 12 0v2M3.5 11a8.5 8.5 0 0 1 17 0v2M8 12v1.5a4 4 0 0 0 8 0V12M5 15v.5a7 7 0 0 0 14 0V15" stroke-linecap="round" /></svg>
                                Simular huella
                            </button>
                        </form>
                    </div>
                </div>

                <div class="relative mt-6 flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm">
                    <span class="font-medium text-slate-300">Último registro</span>
                    <span class="font-semibold text-white">{{ $lastAccess ? ($lastAccess->checked_out_at ? 'Salida '.$lastAccess->checked_out_at->format('H:i') : 'Entrada '.$lastAccess->checked_in_at->format('H:i')) : 'Sin registros de acceso' }}</span>
                </div>
            @else
                <div class="relative mt-8 rounded-2xl border border-dashed border-slate-700 bg-white/5 p-8 text-center text-sm text-slate-300">No hay clientes activos para simular un acceso.</div>
            @endif
        </section>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-600">Resultado de lectura</p>
            <h3 class="mt-1 text-xl font-bold tracking-tight text-slate-950">Estado del acceso</h3>

            @if (session('biometric_result'))
                @php($biometricResult = session('biometric_result'))
                @if ($biometricResult['authorized'])
                    <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-950">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-emerald-500 text-white"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="size-6" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round" /></svg></span>
                        <p class="mt-4 text-sm font-bold uppercase tracking-[0.14em] text-emerald-700">Huella reconocida</p>
                        <p class="mt-1 text-2xl font-bold tracking-tight">Acceso autorizado</p>
                        <dl class="mt-6 space-y-4 border-t border-emerald-200 pt-5 text-sm">
                            <div class="flex items-center justify-between gap-4"><dt class="font-medium text-emerald-800">Cliente</dt><dd class="font-semibold">{{ $biometricResult['client'] }}</dd></div>
                            <div class="flex items-center justify-between gap-4"><dt class="font-medium text-emerald-800">Membresía</dt><dd class="font-semibold">{{ $biometricResult['membership'] }}</dd></div>
                            <div class="flex items-center justify-between gap-4"><dt class="font-medium text-emerald-800">Hora</dt><dd class="font-semibold">{{ $biometricResult['time'] }}</dd></div>
                        </dl>
                        <p class="mt-5 rounded-xl bg-white/70 px-3 py-2 text-sm font-bold text-emerald-800">{{ $biometricResult['result'] }}</p>
                    </div>
                @else
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-red-950">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-red-500 text-white"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="size-6" aria-hidden="true"><path d="M12 8v4m0 4h.01" stroke-linecap="round" /><circle cx="12" cy="12" r="9" /></svg></span>
                        <p class="mt-4 text-sm font-bold uppercase tracking-[0.14em] text-red-700">Huella reconocida</p>
                        <p class="mt-1 text-2xl font-bold tracking-tight">Acceso denegado</p>
                        <p class="mt-4 rounded-xl bg-white/70 px-3 py-3 text-sm font-medium text-red-800">{{ $biometricResult['message'] }}</p>
                    </div>
                @endif
            @else
                <div class="mt-6 flex min-h-72 flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                    <span class="flex size-16 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="size-8" aria-hidden="true"><path d="M9 4.5a3.5 3.5 0 0 1 7 0M6 8a6 6 0 0 1 12 0v2M3.5 11a8.5 8.5 0 0 1 17 0v2M8 12v1.5a4 4 0 0 0 8 0V12" stroke-linecap="round" /></svg></span>
                    <p class="mt-5 text-lg font-bold text-slate-800">Esperando lectura</p>
                    <p class="mt-2 max-w-xs text-sm text-slate-500">Selecciona un cliente y simula su huella para registrar una entrada o salida.</p>
                </div>
            @endif
        </aside>
    </div>

    @if ($selectedClient)
        <script>
            const clientSelector = document.getElementById('client_id');
            const biometricForm = document.getElementById('biometric-access-form');
            const fingerprintButton = document.getElementById('simulate-fingerprint');
            const fingerprintFeedback = document.getElementById('fingerprint-feedback');
            const terminalStatus = document.getElementById('terminal-status');

            clientSelector.addEventListener('change', () => {
                window.location.href = '{{ route('biometric-access.index') }}?client=' + clientSelector.value;
            });

            biometricForm.addEventListener('submit', (event) => {
                event.preventDefault();
                fingerprintButton.disabled = true;
                fingerprintFeedback.textContent = 'Escaneando huella...';
                terminalStatus.textContent = 'Escaneando...';

                window.setTimeout(() => {
                    fingerprintFeedback.textContent = 'Huella reconocida';
                    terminalStatus.textContent = 'Procesando acceso';

                    window.setTimeout(() => biometricForm.submit(), 500);
                }, 900);
            });
        </script>
    @endif
@endsection

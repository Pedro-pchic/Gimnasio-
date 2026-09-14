<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} · @yield('title', 'Administración')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[18rem_minmax(0,1fr)]">
        <aside class="flex h-screen flex-col border-r border-white/5 bg-slate-950 px-4 py-5 text-slate-100 lg:sticky lg:top-0 lg:px-5">
            <a href="{{ route('dashboard') }}" class="group mb-4 flex shrink-0 items-center gap-3 rounded-2xl px-2 py-2 transition hover:bg-white/5">
                <span class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-950/40">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5" aria-hidden="true"><path d="M5 20V10m7 10V4m7 16v-7" stroke-linecap="round" /><path d="m3 10 4-4 5 3 7-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </span>
                <span>
                    <span class="block text-sm font-bold tracking-tight text-white">Gimnasio</span>
                    <span class="block text-xs font-medium text-slate-400">Administración</span>
                </span>
            </a>

            <nav class="sidebar-scroll min-h-0 flex-1 space-y-1 overflow-y-auto pb-6 pr-1" aria-label="Navegación principal">
                @can('dashboard.view')
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
                @endcan

                <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Configuración</p>
                @can('branches.view')<x-nav-link href="{{ route('branches.index') }}" icon="building" :active="request()->routeIs('branches.*')">Sucursales</x-nav-link>@endcan
                @can('services.view')<x-nav-link href="{{ route('services.index') }}" icon="settings" :active="request()->routeIs('services.*')">Servicios</x-nav-link>@endcan

                <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Clientes</p>
                @can('clients.view')<x-nav-link href="{{ route('clients.index') }}" icon="users" :active="request()->routeIs('clients.*')">Clientes</x-nav-link>@endcan
                @can('membership-types.view')<x-nav-link href="{{ route('membership-types.index') }}" icon="card" :active="request()->routeIs('membership-types.*')">Membresías</x-nav-link>@endcan
                @can('benefits.view')<x-nav-link href="{{ route('benefits.index') }}" icon="grid" :active="request()->routeIs('benefits.*')">Beneficios</x-nav-link>@endcan
                @can('client-memberships.view')<x-nav-link href="{{ route('client-memberships.index') }}" icon="card" :active="request()->routeIs('client-memberships.*')">Membresías de clientes</x-nav-link>@endcan
                @can('renewals.view')<x-nav-link href="{{ route('renewals.index') }}" icon="calendar" :active="request()->routeIs('renewals.*')">Renovaciones</x-nav-link>@endcan
                @can('referrals.view')<x-nav-link href="{{ route('referrals.index') }}" icon="users" :active="request()->routeIs('referrals.*')">Referidos</x-nav-link>@endcan

                <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Comercial</p>
                @can('payments.view')<x-nav-link href="{{ route('payments.index') }}" icon="card" :active="request()->routeIs('payments.*')">Pagos</x-nav-link>@endcan
                @can('sales.view')<x-nav-link href="{{ route('sales.index') }}" icon="chart" :active="request()->routeIs('sales.*')">Ventas</x-nav-link>@endcan
                @can('invoices.view')<x-nav-link href="{{ route('invoices.index') }}" icon="grid" :active="request()->routeIs('invoices.*')">Facturación</x-nav-link>@endcan
                @can('commercial-partners.view')<x-nav-link href="{{ route('commercial-partners.index') }}" icon="building" :active="request()->routeIs('commercial-partners.*')">Terceros</x-nav-link>@endcan
                @can('third-party-items.view')<x-nav-link href="{{ route('third-party-items.index') }}" icon="box" :active="request()->routeIs('third-party-items.*')">Productos externos</x-nav-link>@endcan
                @can('discounts.view')<x-nav-link href="{{ route('discounts.index') }}" icon="card" :active="request()->routeIs('discounts.*')">Descuentos</x-nav-link>@endcan

                <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Personal</p>
                @can('employees.view')<x-nav-link href="{{ route('employees.index') }}" icon="users" :active="request()->routeIs('employees.*')">Empleados</x-nav-link>@endcan
                @can('positions.manage')<x-nav-link href="{{ route('positions.index') }}" icon="briefcase" :active="request()->routeIs('positions.*')">Puestos</x-nav-link>@endcan
                @can('work-shifts.manage')<x-nav-link href="{{ route('work-shifts.index') }}" icon="calendar" :active="request()->routeIs('work-shifts.*', 'employee-shift-assignments.*')">Turnos</x-nav-link>@endcan
                @can('employee-attendances.view')<x-nav-link href="{{ route('employee-attendances.index') }}" icon="calendar" :active="request()->routeIs('employee-attendances.*')">Asistencia</x-nav-link>@endcan
                @can('view_bonuses')<x-nav-link href="{{ route('employee-bonuses.index') }}" icon="chart" :active="request()->routeIs('employee-bonuses.*')">Bonos</x-nav-link>@endcan

                <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Operaciones</p>
                @can('biometric-access.view')<x-nav-link href="{{ route('biometric-access.index') }}" icon="scan" :active="request()->routeIs('biometric-access.*')">Control biométrico</x-nav-link>@endcan
                @can('classes.view')<x-nav-link href="{{ route('gym-classes.index') }}" icon="grid" :active="request()->routeIs('gym-classes.*')">Clases</x-nav-link>@endcan
                @can('schedules.view')<x-nav-link href="{{ route('class-schedules.index') }}" icon="calendar" :active="request()->routeIs('class-schedules.*')">Horarios</x-nav-link>@endcan
                @can('enrollments.view')<x-nav-link href="{{ route('class-enrollments.index') }}" icon="users" :active="request()->routeIs('class-enrollments.*')">Participantes</x-nav-link>@endcan
                @can('enrollments.view')<x-nav-link href="{{ route('class-reservations.index') }}" icon="calendar" :active="request()->routeIs('class-reservations.*')">Reservas</x-nav-link>@endcan

                <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Inventario y compras</p>
                @can('view_inventory')<x-nav-link href="{{ route('inventory-items.index') }}" icon="box" :active="request()->routeIs('inventory-items.*')">Inventario</x-nav-link>@endcan
                @can('view_maintenance')<x-nav-link href="{{ route('equipment-maintenances.index') }}" icon="settings" :active="request()->routeIs('equipment-maintenances.*')">Mantenimiento</x-nav-link>@endcan
                @can('view_suppliers')<x-nav-link href="{{ route('suppliers.index') }}" icon="building" :active="request()->routeIs('suppliers.*')">Proveedores</x-nav-link>@endcan
                @can('view_purchases')<x-nav-link href="{{ route('purchase-orders.index') }}" icon="box" :active="request()->routeIs('purchase-orders.*')">Órdenes de compra</x-nav-link>@endcan
                @can('view_purchases')<x-nav-link href="{{ route('quality-certificates.index') }}" icon="grid" :active="request()->routeIs('quality-certificates.*')">Certificados</x-nav-link>@endcan

                <p class="px-3 pt-4 pb-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Reportes</p>
                @can('view_reports')<x-nav-link href="{{ route('reports.index') }}" icon="chart" :active="request()->routeIs('reports.*')">Reportes</x-nav-link>@endcan
            </nav>
        </aside>

        <main class="min-w-0">
            <header class="sticky top-0 z-10 flex min-h-20 items-center justify-between gap-4 border-b border-slate-200/80 bg-white/95 px-5 py-4 backdrop-blur sm:px-8">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Gimnasio / Administración</p>
                    <h1 class="mt-1 truncate text-xl font-bold tracking-tight text-slate-950 sm:text-2xl">@yield('title', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-slate-800">{{ $authenticatedUser->name }}</p>
                        <p class="mt-0.5 text-xs font-medium text-slate-500">{{ $authenticatedUser->roles->pluck('name')->join(' · ') }}</p>
                    </div>
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-sm font-bold text-white shadow-md shadow-blue-200">
                        {{ mb_strtoupper(mb_substr($authenticatedUser->name, 0, 1)) }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex size-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600" title="Cerrar sesión">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-5" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M14 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            <span class="sr-only">Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </header>

            <div class="mx-auto w-full max-w-[1600px] p-5 sm:p-8">
                @if (session('success'))
                    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm" role="status">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 size-4 shrink-0" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 shadow-sm" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 size-4 shrink-0" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 8v4m0 4h.01" stroke-linecap="round" /></svg>
                        <span>Revisa los campos marcados para continuar.</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>

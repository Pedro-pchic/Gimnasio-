<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} · @yield('title', 'Administración')</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
            <aside class="bg-slate-900 p-4 text-slate-100 lg:p-6">
                <a href="{{ route('dashboard') }}" class="mb-6 block text-xl font-bold tracking-tight text-white">
                    Gimnasio · Administración
                </a>

                <nav class="flex flex-wrap gap-2 lg:flex-col lg:gap-1" aria-label="Navegación principal">
                    @can('dashboard.view')
                        <a href="{{ route('dashboard') }}" @class(['rounded-lg px-3 py-2 text-sm font-medium', 'bg-slate-700 text-white' => request()->routeIs('dashboard'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('dashboard')])>Dashboard</a>
                    @endcan
                    @can('branches.view')
                        <a href="{{ route('branches.index') }}" @class(['rounded-lg px-3 py-2 text-sm font-medium', 'bg-slate-700 text-white' => request()->routeIs('branches.*'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('branches.*')])>Sucursales</a>
                    @endcan
                    @can('services.view')
                        <a href="{{ route('services.index') }}" @class(['rounded-lg px-3 py-2 text-sm font-medium', 'bg-slate-700 text-white' => request()->routeIs('services.*'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('services.*')])>Servicios</a>
                    @endcan
                    @can('clients.view')
                        <a href="{{ route('clients.index') }}" @class(['rounded-lg px-3 py-2 text-sm font-medium', 'bg-slate-700 text-white' => request()->routeIs('clients.*'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('clients.*')])>Clientes</a>
                    @endcan
                    @can('membership-types.view')
                        <a href="{{ route('membership-types.index') }}" @class(['rounded-lg px-3 py-2 text-sm font-medium', 'bg-slate-700 text-white' => request()->routeIs('membership-types.*'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('membership-types.*')])>Membresías</a>
                    @endcan
                    @can('benefits.view')
                        <a href="{{ route('benefits.index') }}" @class(['rounded-lg px-3 py-2 text-sm font-medium', 'bg-slate-700 text-white' => request()->routeIs('benefits.*'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('benefits.*')])>Beneficios</a>
                    @endcan
                    @can('client-memberships.view')
                        <a href="{{ route('client-memberships.index') }}" @class(['rounded-lg px-3 py-2 text-sm font-medium', 'bg-slate-700 text-white' => request()->routeIs('client-memberships.*'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('client-memberships.*')])>Membresías de clientes</a>
                    @endcan
                </nav>
            </aside>

            <main class="min-w-0">
                <header class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $authenticatedUser->name }}</p>
                        <p class="text-xs text-slate-500">{{ $authenticatedUser->roles->pluck('name')->join(' · ') }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cerrar sesión</button>
                    </form>
                </header>

                <div class="mx-auto max-w-7xl p-5 sm:p-8">
                    @if (session('success'))
                        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                            Revisa los campos marcados para continuar.
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </body>
</html>

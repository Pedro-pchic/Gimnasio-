@extends('layouts.admin')

@section('title', 'Sucursales')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Operación</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Sucursales</h1>
            <p class="mt-2 text-slate-600">Administra las sedes y los servicios disponibles.</p>
        </div>
        @can('branches.manage')
            <a href="{{ route('branches.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">Nueva sucursal</a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Código</th>
                        <th class="px-5 py-3">Nombre</th>
                        <th class="px-5 py-3">Horario</th>
                        <th class="px-5 py-3">Servicios</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($branches as $branch)
                        <tr>
                            <td class="px-5 py-4 font-mono text-xs text-slate-600">{{ $branch->code }}</td>
                            <td class="px-5 py-4"><a href="{{ route('branches.show', $branch) }}" class="font-semibold text-indigo-700 hover:text-indigo-500">{{ $branch->name }}</a><p class="mt-1 text-xs text-slate-500">{{ $branch->address ?: 'Sin dirección' }}</p></td>
                            <td class="px-5 py-4 text-slate-600">{{ $branch->opening_time ? substr($branch->opening_time, 0, 5) : '—' }} – {{ $branch->closing_time ? substr($branch->closing_time, 0, 5) : '—' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $branch->services->pluck('name')->join(', ') ?: 'Sin servicios' }}</td>
                            <td class="px-5 py-4"><x-status-badge :active="$branch->is_active" /></td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('branches.show', $branch) }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-500">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No hay sucursales registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($branches->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $branches->links() }}</div>@endif
    </div>
@endsection

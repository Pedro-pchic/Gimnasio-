@extends('layouts.admin')

@section('title', 'Empleados')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Personal</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Empleados</h1>
            <p class="mt-2 text-slate-600">Informacion laboral separada de las cuentas de acceso.</p>
        </div>
        @can('employees.manage')
            <a href="{{ route('employees.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">Nuevo empleado</a>
        @endcan
    </div>

    <form method="GET" class="mb-5 flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:flex-row sm:items-end">
        <div class="sm:w-80">
            <label for="branch_id" class="text-sm font-medium text-slate-700">Sucursal</label>
            <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-lg border-slate-300">
                <option value="">Todas las sucursales</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected($selectedBranchId === $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:w-56">
            <label for="status" class="text-sm font-medium text-slate-700">Estado</label>
            <select id="status" name="status" class="mt-1 block w-full rounded-lg border-slate-300">
                <option value="">Todos</option>
                <option value="active" @selected($selectedStatus === 'active')>Activo</option>
                <option value="inactive" @selected($selectedStatus === 'inactive')>Inactivo</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Filtrar</button>
            <a href="{{ route('employees.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Limpiar</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Codigo</th><th class="px-5 py-3">Empleado</th><th class="px-5 py-3">Puesto</th><th class="px-5 py-3">Sucursal</th><th class="px-5 py-3">Usuario</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3"><span class="sr-only">Acciones</span></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($employees as $employee)
                        <tr>
                            <td class="px-5 py-4 font-mono text-xs text-slate-600">{{ $employee->code }}</td>
                            <td class="px-5 py-4"><a href="{{ route('employees.show', $employee) }}" class="font-semibold text-indigo-700 hover:text-indigo-500">{{ $employee->fullName() }}</a><p class="mt-1 text-xs text-slate-500">Contratado: {{ $employee->hired_at->format('d/m/Y') }}</p></td>
                            <td class="px-5 py-4 text-slate-600">{{ $employee->position->name }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $employee->branch->name }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $employee->user?->name ?? 'Sin cuenta asociada' }}</td>
                            <td class="px-5 py-4"><x-status-badge :active="$employee->status === \App\EmployeeStatus::Active" /></td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('employees.show', $employee) }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-500">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-500">No hay empleados registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($employees->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $employees->links() }}</div>@endif
    </div>
@endsection

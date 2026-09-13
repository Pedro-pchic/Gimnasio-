@extends('layouts.admin')

@section('title', 'Mantenimiento')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Equipos</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Mantenimiento</h1>
            <p class="mt-2 text-slate-600">Historial y seguimiento de mantenimientos de equipos.</p>
        </div>
        @can('manage_maintenance')
            <a href="{{ route('equipment-maintenances.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">Registrar mantenimiento</a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Equipo</th><th class="px-5 py-3">Sucursal</th><th class="px-5 py-3">Programado</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3">Costo</th><th class="px-5 py-3"><span class="sr-only">Acciones</span></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($maintenances as $maintenance)
                        <tr>
                            <td class="px-5 py-4"><p class="font-semibold text-slate-800">{{ $maintenance->inventoryItem->name }}</p><p class="mt-0.5 font-mono text-xs text-slate-500">{{ $maintenance->inventoryItem->internal_code }}</p></td>
                            <td class="px-5 py-4 text-slate-600">{{ $maintenance->inventoryItem->branch->name }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $maintenance->scheduled_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $maintenance->status->label() }}</span></td>
                            <td class="px-5 py-4 text-slate-600">{{ $maintenance->cost === null ? '—' : 'Q'.number_format((float) $maintenance->cost, 2) }}</td>
                            <td class="px-5 py-4 text-right">
                                @can('manage_maintenance')
                                    @if ($maintenance->status->value === 'pending')
                                        <div class="flex justify-end gap-2">
                                            <form method="POST" action="{{ route('equipment-maintenances.complete', $maintenance) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="performed_date" value="{{ now()->toDateString() }}">
                                                <button type="submit" class="font-medium text-emerald-700 hover:text-emerald-500">Completar</button>
                                            </form>
                                            <form method="POST" action="{{ route('equipment-maintenances.cancel', $maintenance) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="font-medium text-rose-700 hover:text-rose-500">Cancelar</button>
                                            </form>
                                        </div>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No hay mantenimientos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($maintenances->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">{{ $maintenances->links() }}</div>
        @endif
    </div>
@endsection

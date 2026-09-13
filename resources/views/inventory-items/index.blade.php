@extends('layouts.admin')

@section('title', 'Inventario')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Operación</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Inventario</h1>
            <p class="mt-2 text-slate-600">Artículos, insumos y equipos identificados por su código interno.</p>
        </div>
        @can('manage_inventory')
            <a href="{{ route('inventory-items.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">Nuevo artículo</a>
        @endcan
    </div>

    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('inventory-items.index') }}" @class(['rounded-lg px-3 py-2 text-sm font-medium', 'bg-indigo-600 text-white' => $selectedType === null, 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50' => $selectedType !== null])>Todos</a>
        @foreach ($types as $type)
            <a href="{{ route('inventory-items.index', ['type' => $type->value]) }}" @class(['rounded-lg px-3 py-2 text-sm font-medium', 'bg-indigo-600 text-white' => $selectedType === $type->value, 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50' => $selectedType !== $type->value])>{{ $type->label() }}s</a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Artículo</th><th class="px-5 py-3">Sucursal</th><th class="px-5 py-3">Código / barcode</th><th class="px-5 py-3">Stock</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3"><span class="sr-only">Acciones</span></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($inventoryItems as $inventoryItem)
                        @php($isLowStock = $inventoryItem->quantity !== null && $inventoryItem->minimum_stock !== null && $inventoryItem->quantity <= $inventoryItem->minimum_stock)
                        <tr>
                            <td class="px-5 py-4"><p class="font-semibold text-slate-800">{{ $inventoryItem->name }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $inventoryItem->type->label() }}</p></td>
                            <td class="px-5 py-4 text-slate-600">{{ $inventoryItem->branch->name }}</td>
                            <td class="px-5 py-4 font-mono text-xs text-slate-600">{{ $inventoryItem->internal_code }}</td>
                            <td class="px-5 py-4"><span @class(['font-semibold text-rose-700' => $isLowStock, 'text-slate-700' => ! $isLowStock])>{{ $inventoryItem->quantity === null ? 'No aplica' : $inventoryItem->quantity }}</span>@if ($inventoryItem->minimum_stock !== null)<span class="text-xs text-slate-500"> / mín. {{ $inventoryItem->minimum_stock }}</span>@endif</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $inventoryItem->status->label() }}</span></td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('inventory-items.show', $inventoryItem) }}" class="font-medium text-indigo-700 hover:text-indigo-500">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No hay artículos de inventario registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($inventoryItems->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $inventoryItems->links() }}</div>@endif
    </div>
@endsection

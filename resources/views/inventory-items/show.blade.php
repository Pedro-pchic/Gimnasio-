@extends('layouts.admin')

@section('title', $inventoryItem->name)

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('inventory-items.index') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-500">Volver a inventario</a>
            <h1 class="mt-3 text-3xl font-bold tracking-tight">{{ $inventoryItem->name }}</h1>
            <p class="mt-2 font-mono text-sm text-slate-600">{{ $inventoryItem->internal_code }} · Identificador compatible con barcode</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @can('register_inventory_movements')
                @if ($inventoryItem->quantity !== null)
                    <a href="{{ route('inventory-items.movements', $inventoryItem) }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Movimientos</a>
                @endif
            @endcan
            @can('manage_inventory')
                <a href="{{ route('inventory-items.edit', $inventoryItem) }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Editar</a>
            @endcan
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
            <h2 class="text-lg font-semibold text-slate-800">Información</h2>
            <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sucursal</dt><dd class="mt-1 text-slate-800">{{ $inventoryItem->branch->name }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tipo</dt><dd class="mt-1 text-slate-800">{{ $inventoryItem->type->label() }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estado</dt><dd class="mt-1 text-slate-800">{{ $inventoryItem->status->label() }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Stock</dt><dd class="mt-1 text-slate-800">{{ $inventoryItem->quantity === null ? 'No aplica' : $inventoryItem->quantity }}@if ($inventoryItem->minimum_stock !== null) <span class="text-sm text-slate-500">(mín. {{ $inventoryItem->minimum_stock }})</span>@endif</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Marca / modelo</dt><dd class="mt-1 text-slate-800">{{ $inventoryItem->brand ?: '—' }}{{ $inventoryItem->model ? ' / '.$inventoryItem->model : '' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Serie</dt><dd class="mt-1 text-slate-800">{{ $inventoryItem->serial ?: '—' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Adquisición</dt><dd class="mt-1 text-slate-800">{{ $inventoryItem->acquisition_date?->format('d/m/Y') ?? '—' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Mantenimiento</dt><dd class="mt-1 text-slate-800">{{ $inventoryItem->maintenance_required ? 'Requerido' : 'No requerido' }}</dd></div>
            </dl>
            @if ($inventoryItem->description)
                <div class="mt-5 border-t border-slate-100 pt-5"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Descripción</p><p class="mt-1 whitespace-pre-line text-slate-700">{{ $inventoryItem->description }}</p></div>
            @endif
            @if ($inventoryItem->notes)
                <div class="mt-5 border-t border-slate-100 pt-5"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notas</p><p class="mt-1 whitespace-pre-line text-slate-700">{{ $inventoryItem->notes }}</p></div>
            @endif
        </section>
        <aside class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><h2 class="text-lg font-semibold text-slate-800">Historial</h2><dl class="mt-4 space-y-3 text-sm"><div><dt class="text-slate-500">Movimientos</dt><dd class="font-semibold text-slate-800">{{ $inventoryItem->movements_count }}</dd></div><div><dt class="text-slate-500">Mantenimientos</dt><dd class="font-semibold text-slate-800">{{ $inventoryItem->maintenances_count }}</dd></div></dl></aside>
    </div>

    @if ($inventoryItem->maintenances->isNotEmpty())
        <section class="mt-5 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200"><h2 class="text-lg font-semibold text-slate-800">Mantenimientos recientes</h2><div class="mt-4 divide-y divide-slate-100">@foreach ($inventoryItem->maintenances as $maintenance)<div class="py-3 text-sm"><span class="font-semibold text-slate-800">{{ $maintenance->status->label() }}</span><span class="text-slate-500"> · {{ $maintenance->scheduled_date?->format('d/m/Y') ?? 'Sin fecha programada' }}</span><p class="mt-1 text-slate-600">{{ $maintenance->description }}</p></div>@endforeach</div></section>
    @endif
@endsection

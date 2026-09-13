@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between"><h1 class="text-2xl font-semibold">Proveedores</h1><a href="{{ route('suppliers.create') }}" class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Nuevo proveedor</a></div>
    <div class="overflow-hidden rounded-lg bg-white shadow ring-1 ring-gray-200"><table class="min-w-full divide-y divide-gray-200 text-sm"><thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Nombre</th><th class="px-4 py-3 text-left">Contacto</th><th class="px-4 py-3 text-left">Estado</th><th></th></tr></thead><tbody class="divide-y divide-gray-100">@forelse($suppliers as $supplier)<tr><td class="px-4 py-3">{{ $supplier->name }}</td><td class="px-4 py-3">{{ $supplier->email ?: $supplier->phone ?: '—' }}</td><td class="px-4 py-3">{{ $supplier->is_active ? 'Activo' : 'Inactivo' }}</td><td class="px-4 py-3 text-right"><a class="text-indigo-600" href="{{ route('suppliers.edit', $supplier) }}">Editar</a></td></tr>@empty<tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin proveedores.</td></tr>@endforelse</tbody></table></div>
</div>
@endsection

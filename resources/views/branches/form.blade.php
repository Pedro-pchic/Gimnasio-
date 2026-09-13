@extends('layouts.admin')

@section('title', $branch ? 'Editar sucursal' : 'Nueva sucursal')

@section('content')
    @php($selectedServiceIds = old('service_ids', $branch?->services->modelKeys() ?? []))
    <div class="mb-6">
        <a href="{{ route('branches.index') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-500">← Volver a sucursales</a>
        <h1 class="mt-3 text-3xl font-bold tracking-tight">{{ $branch ? 'Editar sucursal' : 'Nueva sucursal' }}</h1>
    </div>

    <form method="POST" action="{{ $branch ? route('branches.update', $branch) : route('branches.store') }}" class="max-w-4xl rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-7">
        @csrf
        @if ($branch) @method('PUT') @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <div><label for="code" class="text-sm font-medium text-slate-700">Código</label><input id="code" name="code" value="{{ old('code', $branch?->code) }}" required class="mt-1 block w-full rounded-lg border-slate-300"><x-field-error name="code" /></div>
            <div><label for="name" class="text-sm font-medium text-slate-700">Nombre</label><input id="name" name="name" value="{{ old('name', $branch?->name) }}" required class="mt-1 block w-full rounded-lg border-slate-300"><x-field-error name="name" /></div>
            <div class="sm:col-span-2"><label for="address" class="text-sm font-medium text-slate-700">Dirección</label><input id="address" name="address" value="{{ old('address', $branch?->address) }}" class="mt-1 block w-full rounded-lg border-slate-300"><x-field-error name="address" /></div>
            <div><label for="phone" class="text-sm font-medium text-slate-700">Teléfono</label><input id="phone" name="phone" value="{{ old('phone', $branch?->phone) }}" class="mt-1 block w-full rounded-lg border-slate-300"><x-field-error name="phone" /></div>
            <div class="grid grid-cols-2 gap-3"><div><label for="opening_time" class="text-sm font-medium text-slate-700">Abre</label><input id="opening_time" name="opening_time" type="time" value="{{ old('opening_time', $branch?->opening_time ? substr($branch->opening_time, 0, 5) : '') }}" class="mt-1 block w-full rounded-lg border-slate-300"><x-field-error name="opening_time" /></div><div><label for="closing_time" class="text-sm font-medium text-slate-700">Cierra</label><input id="closing_time" name="closing_time" type="time" value="{{ old('closing_time', $branch?->closing_time ? substr($branch->closing_time, 0, 5) : '') }}" class="mt-1 block w-full rounded-lg border-slate-300"><x-field-error name="closing_time" /></div></div>
            <fieldset class="sm:col-span-2"><legend class="text-sm font-medium text-slate-700">Servicios asociados</legend><div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">@foreach ($services as $service)<label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm"><input type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, array_map('intval', $selectedServiceIds), true)) class="rounded border-slate-300 text-indigo-600">{{ $service->name }}</label>@endforeach</div><x-field-error name="service_ids" /></fieldset>
            <label class="flex items-center gap-2 text-sm text-slate-700 sm:col-span-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $branch?->is_active ?? true)) class="rounded border-slate-300 text-indigo-600">Sucursal activa</label>
        </div>
        <div class="mt-7 flex flex-wrap gap-3"><button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ $branch ? 'Guardar cambios' : 'Crear sucursal' }}</button><a href="{{ $branch ? route('branches.show', $branch) : route('branches.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a></div>
    </form>
@endsection

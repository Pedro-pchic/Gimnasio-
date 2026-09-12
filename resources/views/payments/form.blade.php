@extends('layouts.admin')

@section('title', 'Registrar pago')

@section('content')
    <div class="mb-6">
        <a href="{{ route('payments.index') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-500">← Volver a pagos</a>
        <h1 class="mt-3 text-3xl font-bold tracking-tight">Registrar pago</h1>
        <p class="mt-2 text-slate-600">Registra un cobro interno asociado a un cliente.</p>
    </div>

    <form method="POST" action="{{ route('payments.store') }}" class="max-w-4xl rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-7">
        @csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="client_id" class="text-sm font-medium text-slate-700">Cliente</label>
                <select id="client_id" name="client_id" required class="mt-1 block w-full rounded-lg border-slate-300">
                    <option value="">Selecciona un cliente</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected((int) old('client_id') === $client->id)>{{ $client->last_name }}, {{ $client->first_name }} · {{ $client->code }}</option>
                    @endforeach
                </select>
                <x-field-error name="client_id" />
            </div>
            <div>
                <label for="payment_date" class="text-sm font-medium text-slate-700">Fecha</label>
                <input id="payment_date" name="payment_date" type="date" value="{{ old('payment_date', now()->toDateString()) }}" required class="mt-1 block w-full rounded-lg border-slate-300">
                <x-field-error name="payment_date" />
            </div>
            <div>
                <label for="amount" class="text-sm font-medium text-slate-700">Monto</label>
                <input id="amount" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount') }}" required class="mt-1 block w-full rounded-lg border-slate-300">
                <x-field-error name="amount" />
            </div>
            <div>
                <label for="payment_method" class="text-sm font-medium text-slate-700">Método de pago</label>
                <select id="payment_method" name="payment_method" required class="mt-1 block w-full rounded-lg border-slate-300">
                    @foreach ($methods as $method)
                        <option value="{{ $method->value }}" @selected(old('payment_method', 'cash') === $method->value)>{{ ucfirst($method->value) }}</option>
                    @endforeach
                </select>
                <x-field-error name="payment_method" />
            </div>
            <div>
                <label for="status" class="text-sm font-medium text-slate-700">Estado</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-lg border-slate-300">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', 'paid') === $status->value)>{{ ucfirst($status->value) }}</option>
                    @endforeach
                </select>
                <x-field-error name="status" />
            </div>
            <div>
                <label for="reference" class="text-sm font-medium text-slate-700">Referencia</label>
                <input id="reference" name="reference" type="text" value="{{ old('reference') }}" class="mt-1 block w-full rounded-lg border-slate-300">
                <x-field-error name="reference" />
            </div>
            <div class="sm:col-span-2">
                <label for="observations" class="text-sm font-medium text-slate-700">Observaciones</label>
                <textarea id="observations" name="observations" rows="4" class="mt-1 block w-full rounded-lg border-slate-300">{{ old('observations') }}</textarea>
                <x-field-error name="observations" />
            </div>
        </div>
        <div class="mt-7 flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Registrar pago</button>
            <a href="{{ route('payments.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
        </div>
    </form>
@endsection

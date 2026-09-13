@extends('layouts.admin')

@section('title', 'Pago #'.$payment->id)

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div><a href="{{ route('payments.index') }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-500">← Volver a pagos</a><h1 class="mt-3 text-3xl font-bold tracking-tight">Pago #{{ $payment->id }}</h1><p class="mt-2 text-slate-600">Registrado el {{ $payment->payment_date->format('d/m/Y') }} por {{ $payment->user->name }}.</p></div>
        @can('payments.manage')
            @if ($payment->status !== AppPaymentStatus::Cancelled)
                <form method="POST" action="{{ route('payments.cancel', $payment) }}" onsubmit="return confirm('¿Deseas cancelar este pago?');">@csrf @method('PATCH')<button class="rounded-lg border border-red-300 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50">Cancelar pago</button></form>
            @endif
        @endcan
    </div>
    <section class="max-w-3xl rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-7"><dl class="grid gap-5 sm:grid-cols-2"><div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cliente</dt><dd class="mt-1 text-slate-800">{{ $payment->client ? $payment->client->first_name.' '.$payment->client->last_name : 'Venta sin cliente' }}</dd></div><div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Monto</dt><dd class="mt-1 text-2xl font-bold text-slate-900">{{ number_format((float) $payment->amount, 2) }}</dd></div><div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Método</dt><dd class="mt-1 text-slate-800">{{ ucfirst($payment->payment_method->value) }}</dd></div><div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estado</dt><dd class="mt-1 text-slate-800">{{ ucfirst($payment->status->value) }}</dd></div><div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Referencia</dt><dd class="mt-1 text-slate-800">{{ $payment->reference ?: 'No registrada' }}</dd></div><div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Origen</dt><dd class="mt-1 text-slate-800">@if ($payment->sale) Venta <a href="{{ route('sales.show', $payment->sale) }}" class="font-medium text-indigo-700 hover:text-indigo-500">#{{ $payment->sale->id }}</a>@elseif ($payment->clientMembership) Renovación: {{ $payment->clientMembership->membershipType->name }}@else Pago manual @endif</dd></div><div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Observaciones</dt><dd class="mt-1 whitespace-pre-line text-slate-800">{{ $payment->observations ?: 'Sin observaciones' }}</dd></div></dl></section>
@endsection

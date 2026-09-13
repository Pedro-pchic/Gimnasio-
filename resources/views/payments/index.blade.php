@extends('layouts.admin')

@section('title', 'Pagos')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Finanzas</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Pagos</h1>
            <p class="mt-2 text-slate-600">Historial de cobros internos registrados.</p>
        </div>
        @can('payments.manage')
            <a href="{{ route('payments.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">Registrar pago</a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Fecha</th><th class="px-5 py-3">Cliente</th><th class="px-5 py-3">Monto</th><th class="px-5 py-3">Método</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3"><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="px-5 py-4 text-slate-600">{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-4">@if ($payment->client)<a href="{{ route('clients.show', $payment->client) }}" class="font-semibold text-indigo-700 hover:text-indigo-500">{{ $payment->client->first_name }} {{ $payment->client->last_name }}</a>@else<span class="text-slate-500">Venta sin cliente</span>@endif</td>
                            <td class="px-5 py-4 text-slate-600">{{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ ucfirst($payment->payment_method->value) }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ ucfirst($payment->status->value) }}</span></td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('payments.show', $payment) }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-500">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">No hay pagos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($payments->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $payments->links() }}</div>@endif
    </div>
@endsection

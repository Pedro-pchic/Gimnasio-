@extends('layouts.admin')

@section('title', 'Facturación')

@section('content')
    <div class="mb-6">
        <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Comercial</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight">Facturación</h1>
        <p class="mt-2 text-slate-600">Consulta los comprobantes internos generados a partir de ventas y pagos existentes.</p>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">No. comprobante</th>
                        <th class="px-5 py-3">Fecha</th>
                        <th class="px-5 py-3">Cliente</th>
                        <th class="px-5 py-3">Concepto</th>
                        <th class="px-5 py-3 text-right">Subtotal</th>
                        <th class="px-5 py-3 text-right">Descuento</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sales as $sale)
                        @php($documentNumber = $sale->receipt?->number ?? sprintf('FAC-%06d', $sale->id))
                        <tr class="transition hover:bg-blue-50/50">
                            <td class="px-5 py-4 font-semibold text-slate-800">{{ $documentNumber }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $sale->sale_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $sale->client ? $sale->client->first_name.' '.$sale->client->last_name : 'Venta sin cliente' }}</td>
                            <td class="max-w-xs px-5 py-4 text-slate-600">{{ $sale->details->pluck('description')->join(', ') ?: 'Sin detalle registrado' }}</td>
                            <td class="px-5 py-4 text-right text-slate-600">Q{{ number_format((float) $sale->subtotal, 2) }}</td>
                            <td class="px-5 py-4 text-right text-slate-600">Q{{ number_format((float) $sale->discount, 2) }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-slate-900">Q{{ number_format((float) $sale->total, 2) }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-2.5 py-1 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-800' => $sale->status === \App\SaleStatus::Completed,
                                    'bg-red-100 text-red-800' => $sale->status === \App\SaleStatus::Cancelled,
                                    'bg-amber-100 text-amber-800' => $sale->status === \App\SaleStatus::Pending,
                                ])>{{ $sale->status === \App\SaleStatus::Completed ? 'Pagado' : ucfirst($sale->status->value) }}</span>
                            </td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('invoices.show', $sale) }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-500">Ver comprobante</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-10 text-center text-slate-500">No hay ventas registradas para facturar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sales->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">{{ $sales->links() }}</div>
        @endif
    </div>
@endsection

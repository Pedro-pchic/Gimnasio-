@extends('layouts.admin')

@section('title', $sale->receipt?->number ?? sprintf('FAC-%06d', $sale->id))

@section('content')
    @php
        $documentNumber = $sale->receipt?->number ?? sprintf('FAC-%06d', $sale->id);
        $issuedAt = $sale->receipt?->issued_at ?? $sale->sale_date;
        $paymentMethod = $sale->receipt?->payment_method ?? $sale->payments->first()?->payment_method;
        $paymentMethodLabel = match ($paymentMethod?->value) {
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia',
            default => 'No registrado',
        };
        $statusLabel = match ($sale->status) {
            AppSaleStatus::Completed => 'Pagado',
            AppSaleStatus::Cancelled => 'Cancelado',
            default => 'Pendiente',
        };
    @endphp

    <style>
        @media print {
            aside,
            main > header,
            .invoice-actions {
                display: none !important;
            }

            body,
            main {
                background: #ffffff !important;
            }

            main,
            main > div {
                max-width: none !important;
                padding: 0 !important;
            }

            .printable-invoice {
                max-width: none !important;
                border: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>

    <div class="invoice-actions mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-700 transition hover:text-blue-500"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" aria-hidden="true"><path d="m15 18-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>Volver a facturación</a>
            <p class="mt-4 text-sm font-semibold uppercase tracking-[0.16em] text-blue-600">Comprobante interno</p>
            <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">{{ $documentNumber }}</h2>
        </div>
        <button type="button" onclick="window.print()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-300 transition hover:bg-slate-800"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v7H6v-7Z" stroke-linecap="round" stroke-linejoin="round" /></svg>Imprimir comprobante</button>
    </div>

    <article class="printable-invoice max-w-5xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200">
        <header class="flex flex-col gap-6 bg-slate-950 px-6 py-7 text-white sm:flex-row sm:items-start sm:justify-between sm:px-10 sm:py-9">
            <div>
                <div class="flex items-center gap-3">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-blue-500 text-white"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5" aria-hidden="true"><path d="M5 20V10m7 10V4m7 16v-7" stroke-linecap="round" /><path d="m3 10 4-4 5 3 7-6" stroke-linecap="round" stroke-linejoin="round" /></svg></span>
                    <div><p class="text-lg font-bold tracking-tight">Gimnasio</p><p class="text-xs font-medium text-slate-400">Administración</p></div>
                </div>
                <p class="mt-7 text-xs font-bold uppercase tracking-[0.2em] text-blue-300">Comprobante interno</p>
                <h3 class="mt-2 text-3xl font-bold tracking-tight">{{ $documentNumber }}</h3>
                <p class="mt-3 text-sm text-slate-300">Comprobante interno - prototipo académico</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm sm:min-w-52">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Fecha de emisión</p>
                <p class="mt-2 text-lg font-bold">{{ $issuedAt->format('d/m/Y') }}</p>
                <p class="mt-4 text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Estado</p>
                <p @class([
                    'mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-bold',
                    'bg-emerald-400/15 text-emerald-300' => $sale->status === AppSaleStatus::Completed,
                    'bg-red-400/15 text-red-300' => $sale->status === AppSaleStatus::Cancelled,
                    'bg-amber-400/15 text-amber-300' => $sale->status === AppSaleStatus::Pending,
                ])>{{ $statusLabel }}</p>
            </div>
        </header>

        <div class="p-6 sm:p-10">
            <section class="grid gap-5 border-b border-slate-200 pb-6 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Facturado a</p>
                    <p class="mt-2 text-xl font-bold tracking-tight text-slate-950">{{ $sale->client ? $sale->client->first_name.' '.$sale->client->last_name : 'Venta sin cliente' }}</p>
                </div>
                <div class="sm:text-right">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Método de pago</p>
                    <p class="mt-2 text-lg font-semibold text-slate-800">{{ $paymentMethodLabel }}</p>
                </div>
            </section>

            <section class="mt-7">
                <div class="flex items-center justify-between gap-4">
                    <h4 class="text-sm font-bold uppercase tracking-[0.14em] text-slate-700">Detalle de cobro</h4>
                    <span class="text-sm font-medium text-slate-500">{{ $sale->details->count() }} conceptos</span>
                </div>
                <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr><th class="px-4 py-3.5 sm:px-5">Concepto</th><th class="px-4 py-3.5 text-right sm:px-5">Cantidad</th><th class="px-4 py-3.5 text-right sm:px-5">Precio</th><th class="px-4 py-3.5 text-right sm:px-5">Importe</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($sale->details as $detail)
                                <tr class="transition hover:bg-blue-50/40">
                                    <td class="px-4 py-4 font-medium text-slate-800 sm:px-5">{{ $detail->description }}</td>
                                    <td class="px-4 py-4 text-right text-slate-500 sm:px-5">{{ number_format((float) $detail->quantity, 2) }}</td>
                                    <td class="px-4 py-4 text-right text-slate-500 sm:px-5">Q{{ number_format((float) $detail->unit_price, 2) }}</td>
                                    <td class="px-4 py-4 text-right font-bold text-slate-900 sm:px-5">Q{{ number_format((float) $detail->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="mt-7 flex justify-end">
                <dl class="w-full max-w-sm space-y-3 rounded-2xl bg-slate-50 p-5 text-sm">
                    <div class="flex justify-between gap-6"><dt class="font-medium text-slate-500">Subtotal</dt><dd class="font-semibold text-slate-800">Q{{ number_format((float) $sale->subtotal, 2) }}</dd></div>
                    <div class="flex justify-between gap-6"><dt class="font-medium text-slate-500">Descuento</dt><dd class="font-semibold text-slate-800">Q{{ number_format((float) $sale->discount, 2) }}</dd></div>
                    <div class="flex justify-between gap-6 border-t border-slate-200 pt-4 text-lg"><dt class="font-bold text-slate-950">TOTAL</dt><dd class="font-bold text-slate-950">Q{{ number_format((float) $sale->total, 2) }}</dd></div>
                </dl>
            </div>

            <footer class="mt-8 border-t border-dashed border-slate-300 pt-5 text-center text-xs text-slate-400">Documento generado para control administrativo interno.</footer>
        </div>
    </article>
@endsection

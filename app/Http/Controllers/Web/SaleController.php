<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSaleRequest;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Sale;
use App\PaymentMethod;
use App\PaymentStatus;
use App\SaleStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(): View
    {
        return view('sales.index', [
            'sales' => Sale::query()
                ->with(['client', 'branch', 'user', 'receipt'])
                ->orderByDesc('sale_date')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $details = collect($data['details'])->map(function (array $detail): array {
            $subtotal = round((float) $detail['quantity'] * (float) $detail['unit_price'], 2);

            return [
                ...$detail,
                'subtotal' => $subtotal,
            ];
        });
        $subtotal = round((float) $details->sum('subtotal'), 2);
        $discount = round((float) ($data['discount'] ?? 0), 2);
        $total = round($subtotal - $discount, 2);

        $sale = DB::transaction(function () use ($data, $details, $subtotal, $discount, $total, $request): Sale {
            $sale = Sale::query()->create([
                'client_id' => $data['client_id'] ?? null,
                'branch_id' => $data['branch_id'],
                'user_id' => $request->user()->getKey(),
                'sale_date' => $data['sale_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => SaleStatus::Completed,
            ]);

            $sale->details()->createMany($details->all());

            Payment::query()->create([
                'client_id' => $data['client_id'] ?? null,
                'user_id' => $request->user()->getKey(),
                'sale_id' => $sale->getKey(),
                'payment_date' => $data['sale_date'],
                'amount' => $total,
                'payment_method' => $data['payment_method'],
                'reference' => $data['reference'] ?? null,
                'status' => PaymentStatus::Paid,
                'observations' => $data['observations'] ?? null,
            ]);

            Receipt::query()->create([
                'sale_id' => $sale->getKey(),
                'number' => sprintf('COMP-%06d', $sale->getKey()),
                'issued_at' => $data['sale_date'],
                'payment_method' => $data['payment_method'],
            ]);

            return $sale;
        });

        return redirect()->route('sales.receipt', $sale)->with('success', 'Venta registrada y comprobante generado correctamente.');
    }

    public function show(Sale $sale): View
    {
        return view('sales.show', [
            'sale' => $sale->load(['client', 'branch', 'user', 'details', 'payments', 'receipt']),
        ]);
    }

    public function cancel(Sale $sale): RedirectResponse
    {
        DB::transaction(function () use ($sale): void {
            $sale->update(['status' => SaleStatus::Cancelled]);
            $sale->payments()->where('status', '!=', PaymentStatus::Cancelled->value)->update([
                'status' => PaymentStatus::Cancelled,
            ]);
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Venta cancelada correctamente.');
    }

    public function receipt(Sale $sale): View
    {
        return view('sales.receipt', [
            'sale' => $sale->load(['client', 'branch', 'user', 'details', 'payments', 'receipt']),
        ]);
    }

    private function formView(): View
    {
        return view('sales.form', [
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->orderBy('id')->get(),
            'clients' => Client::query()->where('is_active', true)->orderBy('last_name')->orderBy('first_name')->orderBy('id')->get(),
            'methods' => PaymentMethod::cases(),
        ]);
    }
}

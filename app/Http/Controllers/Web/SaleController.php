<?php

namespace App\Http\Controllers\Web;

use App\DiscountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSaleRequest;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Discount;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Sale;
use App\Models\ThirdPartyItem;
use App\PaymentMethod;
use App\PaymentStatus;
use App\SaleDetailType;
use App\SaleStatus;
use App\ThirdPartyItemType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
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
        $hasThirdPartyDetails = collect($data['details'])
            ->contains(fn (array $detail): bool => in_array($detail['concept_type'], SaleDetailType::thirdPartyValues(), true));

        if ($hasThirdPartyDetails) {
            Gate::authorize('third-party-sales.manage');
        }

        $sale = DB::transaction(function () use ($data, $request): Sale {
            $details = $this->resolveDetails($data['details'], (int) $data['branch_id'], $data['sale_date']);
            $subtotal = round((float) $details->sum('subtotal'), 2);
            $discount = round((float) ($data['discount'] ?? 0), 2);

            if ($discount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount' => 'El descuento no puede superar el subtotal de la venta.',
                ]);
            }

            $total = round($subtotal - $discount, 2);

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
        }, attempts: 3);

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
            'thirdPartyItems' => ThirdPartyItem::query()
                ->with(['commercialPartner', 'branches'])
                ->where('is_active', true)
                ->whereHas('commercialPartner', fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->orderBy('id')
                ->get(),
            'methods' => PaymentMethod::cases(),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $details
     * @return Collection<int, array<string, mixed>>
     */
    private function resolveDetails(array $details, int $branchId, string $saleDate): Collection
    {
        return collect($details)->map(function (array $detail, int $index) use ($branchId, $saleDate): array {
            $conceptType = SaleDetailType::from($detail['concept_type']);

            if (! in_array($conceptType, [SaleDetailType::ThirdPartyProduct, SaleDetailType::ThirdPartyService], true)) {
                $subtotal = round((float) $detail['quantity'] * (float) $detail['unit_price'], 2);

                return [
                    'concept_type' => $conceptType,
                    'concept_reference_id' => $detail['concept_reference_id'] ?? null,
                    'description' => $detail['description'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'discount' => 0,
                    'subtotal' => $subtotal,
                ];
            }

            $item = ThirdPartyItem::query()
                ->with('commercialPartner')
                ->lockForUpdate()
                ->find($detail['concept_reference_id']);

            if ($item === null || ! $item->is_active || ! $item->commercialPartner->is_active) {
                throw ValidationException::withMessages([
                    "details.{$index}.concept_reference_id" => 'El producto o servicio externo no está disponible.',
                ]);
            }

            $expectedType = $conceptType === SaleDetailType::ThirdPartyProduct
                ? ThirdPartyItemType::Product
                : ThirdPartyItemType::Service;

            if ($item->type !== $expectedType) {
                throw ValidationException::withMessages([
                    "details.{$index}.concept_type" => 'El tipo de concepto no coincide con el artículo externo seleccionado.',
                ]);
            }

            if (! $item->branches()->whereKey($branchId)->exists()) {
                throw ValidationException::withMessages([
                    "details.{$index}.concept_reference_id" => 'El artículo externo no está disponible en la sucursal seleccionada.',
                ]);
            }

            $quantity = (float) $detail['quantity'];
            $unitPrice = (float) $item->base_price;
            $grossSubtotal = round($quantity * $unitPrice, 2);
            $lineDiscount = $this->lineDiscount($item, $branchId, $saleDate, $grossSubtotal);

            return [
                'concept_type' => $conceptType,
                'concept_reference_id' => $item->getKey(),
                'description' => "{$item->name} - {$item->commercialPartner->name}",
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $lineDiscount,
                'subtotal' => round(max(0, $grossSubtotal - $lineDiscount), 2),
            ];
        });
    }

    private function lineDiscount(ThirdPartyItem $item, int $branchId, string $saleDate, float $grossSubtotal): float
    {
        $bestDiscount = 0.0;

        $item->discounts()
            ->applicableOn($saleDate)
            ->where(function (Builder $query) use ($branchId): void {
                $query
                    ->whereDoesntHave('branches')
                    ->orWhereHas('branches', fn (Builder $query) => $query->whereKey($branchId));
            })
            ->lockForUpdate()
            ->orderBy('discounts.id')
            ->each(function (Discount $discount) use (&$bestDiscount, $grossSubtotal): void {
                $amount = match ($discount->type) {
                    DiscountType::Percentage => $grossSubtotal * ((float) $discount->value / 100),
                    DiscountType::FixedAmount => (float) $discount->value,
                };

                $bestDiscount = max($bestDiscount, min($grossSubtotal, round($amount, 2)));
            });

        return round($bestDiscount, 2);
    }
}

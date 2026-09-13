<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePurchaseOrderRequest;
use App\InventoryMovementType;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\PurchaseOrderStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(): View
    {
        return view('purchase-orders.index', ['purchaseOrders' => PurchaseOrder::query()->with(['supplier', 'branch'])->latest('id')->paginate(15)]);
    }

    public function create(): View
    {
        return view('purchase-orders.form', [
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'inventoryItems' => InventoryItem::query()->whereIn('type', ['product', 'supply'])->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'];
        unset($data['items']);

        $purchaseOrder = DB::transaction(function () use ($data, $items, $request): PurchaseOrder {
            $total = 0;
            foreach ($items as $item) {
                $total += ((float) $item['quantity']) * ((float) $item['unit_cost']);
            }

            $purchaseOrder = PurchaseOrder::query()->create([
                ...$data,
                'user_id' => $request->user()->id,
                'total' => round($total, 2),
                'status' => PurchaseOrderStatus::Pending,
            ]);

            foreach ($items as $item) {
                $purchaseOrder->items()->create([
                    ...$item,
                    'subtotal' => round(((float) $item['quantity']) * ((float) $item['unit_cost']), 2),
                ]);
            }

            return $purchaseOrder;
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Orden de compra creada correctamente.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        return view('purchase-orders.show', ['purchaseOrder' => $purchaseOrder->load(['supplier', 'branch', 'user', 'items.inventoryItem', 'qualityCertificates'])]);
    }

    public function receive(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        DB::transaction(function () use ($purchaseOrder): void {
            $order = PurchaseOrder::query()->lockForUpdate()->findOrFail($purchaseOrder->id);

            if ($order->status !== PurchaseOrderStatus::Pending) {
                throw ValidationException::withMessages(['purchase_order' => 'La orden ya fue recibida o cancelada.']);
            }

            $items = $order->items()->lockForUpdate()->get();
            foreach ($items as $orderItem) {
                $inventoryItem = InventoryItem::query()->lockForUpdate()->findOrFail($orderItem->inventory_item_id);
                if ($inventoryItem->branch_id !== $order->branch_id) {
                    throw ValidationException::withMessages(['purchase_order' => 'Todos los artículos deben pertenecer a la sucursal de la orden.']);
                }

                $currentQuantity = $inventoryItem->quantity ?? 0;
                $inventoryItem->update(['quantity' => $currentQuantity + $orderItem->quantity]);
                $inventoryItem->movements()->create([
                    'type' => InventoryMovementType::Entry,
                    'quantity' => $orderItem->quantity,
                    'reason' => "Recepción de orden #{$order->id}",
                    'user_id' => auth()->id(),
                ]);
            }

            $order->update(['status' => PurchaseOrderStatus::Received, 'received_at' => now()]);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Orden recibida e inventario actualizado.');
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreInventoryItemRequest;
use App\Http\Requests\Api\V1\StoreInventoryMovementRequest;
use App\Http\Requests\Api\V1\UpdateInventoryItemRequest;
use App\InventoryItemType;
use App\InventoryMovementType;
use App\Models\Branch;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function index(Request $request): View
    {
        $availableTypes = array_map(
            fn (InventoryItemType $type): string => $type->value,
            InventoryItemType::cases(),
        );
        $selectedType = $request->query('type');

        if (! in_array($selectedType, $availableTypes, true)) {
            $selectedType = null;
        }

        return view('inventory-items.index', [
            'inventoryItems' => InventoryItem::query()
                ->with('branch')
                ->withCount(['movements', 'maintenances'])
                ->when($selectedType, fn ($query) => $query->where('type', $selectedType))
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'selectedType' => $selectedType,
            'types' => InventoryItemType::cases(),
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StoreInventoryItemRequest $request): RedirectResponse
    {
        $inventoryItem = InventoryItem::query()->create($request->validated());

        return redirect()->route('inventory-items.show', $inventoryItem)->with('success', 'Artículo de inventario creado correctamente.');
    }

    public function show(InventoryItem $inventoryItem): View
    {
        return view('inventory-items.show', [
            'inventoryItem' => $inventoryItem->load([
                'branch',
                'maintenances' => fn ($query) => $query->latest('id')->limit(5),
            ])->loadCount(['movements', 'maintenances']),
        ]);
    }

    public function edit(InventoryItem $inventoryItem): View
    {
        return $this->formView($inventoryItem);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $data = $request->validated();
        unset($data['quantity']);
        $inventoryItem->update($data);

        return redirect()->route('inventory-items.show', $inventoryItem)->with('success', 'Artículo de inventario actualizado correctamente.');
    }

    public function movements(InventoryItem $inventoryItem): View
    {
        return view('inventory-items.movements', [
            'inventoryItem' => $inventoryItem->load('branch'),
            'movements' => $inventoryItem->movements()
                ->with('user')
                ->latest('id')
                ->paginate(15),
            'movementTypes' => InventoryMovementType::cases(),
        ]);
    }

    public function storeMovement(StoreInventoryMovementRequest $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $inventoryItem, $request): void {
            $lockedInventoryItem = InventoryItem::query()->lockForUpdate()->findOrFail($inventoryItem->id);

            if ($lockedInventoryItem->quantity === null) {
                throw ValidationException::withMessages([
                    'quantity' => 'Este artículo no maneja stock.',
                ]);
            }

            $movementType = InventoryMovementType::from($data['type']);
            $currentQuantity = $lockedInventoryItem->quantity;
            $movementQuantity = $data['quantity'];

            if ($movementType === InventoryMovementType::Exit && $movementQuantity > $currentQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'La salida solicitada supera el stock disponible.',
                ]);
            }

            $newQuantity = match ($movementType) {
                InventoryMovementType::Entry => $currentQuantity + $movementQuantity,
                InventoryMovementType::Exit => $currentQuantity - $movementQuantity,
                InventoryMovementType::Adjustment => $movementQuantity,
            };

            $lockedInventoryItem->update(['quantity' => $newQuantity]);
            $lockedInventoryItem->movements()->create([
                'type' => $movementType,
                'quantity' => $movementQuantity,
                'reason' => $data['reason'] ?? null,
                'user_id' => $request->user()->id,
            ]);
        });

        return redirect()->route('inventory-items.movements', $inventoryItem)->with('success', 'Movimiento de inventario registrado correctamente.');
    }

    private function formView(?InventoryItem $inventoryItem = null): View
    {
        return view('inventory-items.form', [
            'inventoryItem' => $inventoryItem,
            'branches' => Branch::query()->orderBy('name')->orderBy('id')->get(),
            'types' => InventoryItemType::cases(),
        ]);
    }
}

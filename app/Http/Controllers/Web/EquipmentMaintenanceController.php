<?php

namespace App\Http\Controllers\Web;

use App\EquipmentMaintenanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CompleteEquipmentMaintenanceRequest;
use App\Http\Requests\Api\V1\StoreEquipmentMaintenanceRequest;
use App\InventoryItemStatus;
use App\InventoryItemType;
use App\Models\EquipmentMaintenance;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EquipmentMaintenanceController extends Controller
{
    public function index(): View
    {
        return view('equipment-maintenances.index', [
            'maintenances' => EquipmentMaintenance::query()
                ->with('inventoryItem.branch')
                ->latest('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('equipment-maintenances.form', [
            'equipmentItems' => $this->equipmentItems(),
            'statuses' => EquipmentMaintenanceStatus::cases(),
        ]);
    }

    public function store(StoreEquipmentMaintenanceRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $maintenance = DB::transaction(function () use ($data): EquipmentMaintenance {
            $inventoryItem = InventoryItem::query()->lockForUpdate()->findOrFail($data['inventory_item_id']);

            if ($inventoryItem->type !== InventoryItemType::Equipment) {
                throw ValidationException::withMessages([
                    'inventory_item_id' => 'El mantenimiento solo puede registrarse para equipos.',
                ]);
            }

            $maintenance = $inventoryItem->maintenances()->create($data);

            if ($maintenance->status === EquipmentMaintenanceStatus::Pending && $inventoryItem->status === InventoryItemStatus::Active) {
                $inventoryItem->update(['status' => InventoryItemStatus::Maintenance]);
            }

            return $maintenance;
        });

        return redirect()->route('equipment-maintenances.index')->with('success', "Mantenimiento #{$maintenance->id} registrado correctamente.");
    }

    public function complete(CompleteEquipmentMaintenanceRequest $request, EquipmentMaintenance $equipmentMaintenance): RedirectResponse
    {
        DB::transaction(function () use ($request, $equipmentMaintenance): void {
            $maintenance = EquipmentMaintenance::query()->lockForUpdate()->findOrFail($equipmentMaintenance->id);

            if ($maintenance->status !== EquipmentMaintenanceStatus::Pending) {
                throw ValidationException::withMessages([
                    'maintenance' => 'Solo se pueden completar mantenimientos pendientes.',
                ]);
            }

            $inventoryItem = InventoryItem::query()->lockForUpdate()->findOrFail($maintenance->inventory_item_id);
            $maintenance->update([
                'status' => EquipmentMaintenanceStatus::Completed,
                'performed_date' => $request->validated('performed_date'),
            ]);

            $this->restoreEquipmentStatusWhenNoMaintenanceIsPending($inventoryItem);
        });

        return redirect()->route('equipment-maintenances.index')->with('success', 'Mantenimiento completado correctamente.');
    }

    public function cancel(EquipmentMaintenance $equipmentMaintenance): RedirectResponse
    {
        DB::transaction(function () use ($equipmentMaintenance): void {
            $maintenance = EquipmentMaintenance::query()->lockForUpdate()->findOrFail($equipmentMaintenance->id);

            if ($maintenance->status !== EquipmentMaintenanceStatus::Pending) {
                throw ValidationException::withMessages([
                    'maintenance' => 'Solo se pueden cancelar mantenimientos pendientes.',
                ]);
            }

            $inventoryItem = InventoryItem::query()->lockForUpdate()->findOrFail($maintenance->inventory_item_id);
            $maintenance->update(['status' => EquipmentMaintenanceStatus::Cancelled]);

            $this->restoreEquipmentStatusWhenNoMaintenanceIsPending($inventoryItem);
        });

        return redirect()->route('equipment-maintenances.index')->with('success', 'Mantenimiento cancelado correctamente.');
    }

    /**
     * @return Collection<int, InventoryItem>
     */
    private function equipmentItems(): Collection
    {
        return InventoryItem::query()
            ->with('branch')
            ->where('type', InventoryItemType::Equipment->value)
            ->where('status', '!=', InventoryItemStatus::Inactive->value)
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }

    private function restoreEquipmentStatusWhenNoMaintenanceIsPending(InventoryItem $inventoryItem): void
    {
        if ($inventoryItem->status !== InventoryItemStatus::Maintenance) {
            return;
        }

        $hasPendingMaintenance = $inventoryItem->maintenances()
            ->where('status', EquipmentMaintenanceStatus::Pending->value)
            ->exists();

        if (! $hasPendingMaintenance) {
            $inventoryItem->update(['status' => InventoryItemStatus::Active]);
        }
    }
}

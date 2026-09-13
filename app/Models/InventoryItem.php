<?php

namespace App\Models;

use App\InventoryItemStatus;
use App\InventoryItemType;
use Database\Factories\InventoryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['branch_id', 'name', 'type', 'internal_code', 'quantity', 'minimum_stock', 'status', 'notes', 'description', 'acquisition_date', 'brand', 'model', 'serial', 'maintenance_required'])]
class InventoryItem extends Model
{
    /** @use HasFactory<InventoryItemFactory> */
    use HasFactory;

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(EquipmentMaintenance::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'type' => InventoryItemType::class,
            'quantity' => 'integer',
            'minimum_stock' => 'integer',
            'status' => InventoryItemStatus::class,
            'acquisition_date' => 'date',
            'maintenance_required' => 'boolean',
        ];
    }
}

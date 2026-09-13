<?php

namespace App\Models;

use App\EquipmentMaintenanceStatus;
use Database\Factories\EquipmentMaintenanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['inventory_item_id', 'scheduled_date', 'performed_date', 'description', 'status', 'cost', 'notes'])]
class EquipmentMaintenance extends Model
{
    /** @use HasFactory<EquipmentMaintenanceFactory> */
    use HasFactory;

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'performed_date' => 'date',
            'status' => EquipmentMaintenanceStatus::class,
            'cost' => 'decimal:2',
        ];
    }
}

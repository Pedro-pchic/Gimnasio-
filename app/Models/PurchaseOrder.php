<?php

namespace App\Models;

use App\PurchaseOrderStatus;
use Database\Factories\PurchaseOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['supplier_id', 'branch_id', 'user_id', 'order_date', 'status', 'total', 'received_at', 'notes'])]
class PurchaseOrder extends Model
{
    /** @use HasFactory<PurchaseOrderFactory> */
    use HasFactory;

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function qualityCertificates(): HasMany
    {
        return $this->hasMany(QualityCertificate::class);
    }

    /** @return array<string, string|class-string> */
    protected function casts(): array
    {
        return ['order_date' => 'date', 'received_at' => 'datetime', 'status' => PurchaseOrderStatus::class, 'total' => 'decimal:2'];
    }
}

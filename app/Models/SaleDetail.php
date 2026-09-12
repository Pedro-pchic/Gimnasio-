<?php

namespace App\Models;

use App\SaleDetailType;
use Database\Factories\SaleDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sale_id', 'concept_type', 'concept_reference_id', 'description', 'quantity', 'unit_price', 'subtotal'])]
class SaleDetail extends Model
{
    /** @use HasFactory<SaleDetailFactory> */
    use HasFactory;

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'concept_type' => SaleDetailType::class,
        ];
    }
}

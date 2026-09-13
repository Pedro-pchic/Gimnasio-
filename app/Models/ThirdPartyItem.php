<?php

namespace App\Models;

use App\ThirdPartyItemType;
use Database\Factories\ThirdPartyItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['third_party_id', 'name', 'description', 'type', 'base_price', 'is_active'])]
class ThirdPartyItem extends Model
{
    /** @use HasFactory<ThirdPartyItemFactory> */
    use HasFactory;

    public function commercialPartner(): BelongsTo
    {
        return $this->belongsTo(CommercialPartner::class, 'third_party_id');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class)->withTimestamps();
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class)->withTimestamps();
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'type' => ThirdPartyItemType::class,
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}

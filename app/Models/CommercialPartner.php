<?php

namespace App\Models;

use App\CommercialPartnerType;
use Database\Factories\CommercialPartnerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'contact', 'phone', 'email', 'observations', 'is_active'])]
class CommercialPartner extends Model
{
    /** @use HasFactory<CommercialPartnerFactory> */
    use HasFactory;

    public function thirdPartyItems(): HasMany
    {
        return $this->hasMany(ThirdPartyItem::class, 'third_party_id');
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'type' => CommercialPartnerType::class,
            'is_active' => 'boolean',
        ];
    }
}

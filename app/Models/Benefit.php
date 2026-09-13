<?php

namespace App\Models;

use Database\Factories\BenefitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'description', 'benefit_type', 'value', 'usage_limit', 'usage_period', 'is_active'])]
class Benefit extends Model
{
    /** @use HasFactory<BenefitFactory> */
    use HasFactory;

    public function membershipTypes(): BelongsToMany
    {
        return $this->belongsToMany(MembershipType::class)->withTimestamps();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}

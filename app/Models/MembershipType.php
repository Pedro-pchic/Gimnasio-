<?php

namespace App\Models;

use Database\Factories\MembershipTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'reference_price', 'is_active'])]
class MembershipType extends Model
{
    /** @use HasFactory<MembershipTypeFactory> */
    use HasFactory;

    public function benefits(): BelongsToMany
    {
        return $this->belongsToMany(Benefit::class)->withTimestamps();
    }

    public function clientMemberships(): HasMany
    {
        return $this->hasMany(ClientMembership::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reference_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}

<?php

namespace App\Models;

use App\DiscountType;
use Carbon\CarbonInterface;
use Database\Factories\DiscountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'type', 'value', 'start_date', 'end_date', 'is_active'])]
class Discount extends Model
{
    /** @use HasFactory<DiscountFactory> */
    use HasFactory;

    public function thirdPartyItems(): BelongsToMany
    {
        return $this->belongsToMany(ThirdPartyItem::class)->withTimestamps();
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class)->withTimestamps();
    }

    /**
     * @param  Builder<Discount>  $query
     * @return Builder<Discount>
     */
    public function scopeApplicableOn(Builder $query, CarbonInterface|string $date): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('start_date')->orWhereDate('start_date', '<=', $date);
            })
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('end_date')->orWhereDate('end_date', '>=', $date);
            });
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'type' => DiscountType::class,
            'value' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }
}

<?php

namespace App\Models;

use Database\Factories\ReferralRewardUseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['referral_id', 'sale_id', 'amount'])]
class ReferralRewardUse extends Model
{
    /** @use HasFactory<ReferralRewardUseFactory> */
    use HasFactory;

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }
}

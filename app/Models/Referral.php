<?php

namespace App\Models;

use App\ReferralRewardStatus;
use App\ReferralStatus;
use Database\Factories\ReferralFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['referrer_client_id', 'referred_client_id', 'status', 'reward_amount', 'reward_status'])]
class Referral extends Model
{
    /** @use HasFactory<ReferralFactory> */
    use HasFactory;

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'referrer_client_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'referred_client_id');
    }

    public function rewardUses(): HasMany
    {
        return $this->hasMany(ReferralRewardUse::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReferralStatus::class,
            'reward_amount' => 'decimal:2',
            'reward_status' => ReferralRewardStatus::class,
        ];
    }
}

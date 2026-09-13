<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['branch_id', 'code', 'first_name', 'last_name', 'birth_date', 'phone', 'email', 'address', 'registration_date', 'is_active'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ClientMembership::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function referralReceived(): HasOne
    {
        return $this->hasOne(Referral::class, 'referred_client_id');
    }

    public function referralsSent(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_client_id');
    }

    public function gymClassEnrollments(): HasMany
    {
        return $this->hasMany(GymClassEnrollment::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'registration_date' => 'date',
            'is_active' => 'boolean',
        ];
    }
}

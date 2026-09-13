<?php

namespace App\Models;

use App\ClientMembershipStatus;
use Database\Factories\ClientMembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['client_id', 'membership_type_id', 'start_date', 'end_date', 'applied_price', 'status', 'observations'])]
class ClientMembership extends Model
{
    /** @use HasFactory<ClientMembershipFactory> */
    use HasFactory;

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'applied_price' => 'decimal:2',
            'status' => ClientMembershipStatus::class,
        ];
    }
}

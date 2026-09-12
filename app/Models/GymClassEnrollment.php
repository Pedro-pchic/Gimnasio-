<?php

namespace App\Models;

use App\GymClassEnrollmentStatus;
use Database\Factories\GymClassEnrollmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_id', 'gym_class_schedule_id', 'enrollment_date', 'status'])]
class GymClassEnrollment extends Model
{
    /** @use HasFactory<GymClassEnrollmentFactory> */
    use HasFactory;

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function gymClassSchedule(): BelongsTo
    {
        return $this->belongsTo(GymClassSchedule::class);
    }

    public function scopeCapacityBlocking(Builder $query): Builder
    {
        return $query->whereIn('status', GymClassEnrollmentStatus::capacityBlockingValues());
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'enrollment_date' => 'date',
            'status' => GymClassEnrollmentStatus::class,
        ];
    }
}

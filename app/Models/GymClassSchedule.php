<?php

namespace App\Models;

use App\GymClassEnrollmentStatus;
use Carbon\CarbonInterface;
use Database\Factories\GymClassScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['gym_class_id', 'day_of_week', 'start_time', 'end_time', 'is_active'])]
class GymClassSchedule extends Model
{
    /** @use HasFactory<GymClassScheduleFactory> */
    use HasFactory;

    public function gymClass(): BelongsTo
    {
        return $this->belongsTo(GymClass::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(GymClassEnrollment::class);
    }

    public function availableSeatsFor(CarbonInterface|string $date): int
    {
        $enrolledCount = $this->enrollments()
            ->whereDate('enrollment_date', $date)
            ->whereIn('status', GymClassEnrollmentStatus::capacityBlockingValues())
            ->count();

        return max(0, $this->gymClass->maximum_capacity - $enrolledCount);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}

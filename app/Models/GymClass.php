<?php

namespace App\Models;

use Database\Factories\GymClassFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

#[Fillable(['branch_id', 'instructor_employee_id', 'name', 'description', 'type', 'maximum_capacity', 'requires_premium', 'is_active'])]
class GymClass extends Model
{
    /** @use HasFactory<GymClassFactory> */
    use HasFactory;

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'instructor_employee_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(GymClassSchedule::class);
    }

    public function enrollments(): HasManyThrough
    {
        return $this->hasManyThrough(GymClassEnrollment::class, GymClassSchedule::class);
    }

    /**
     * @return array<int, string>
     */
    public static function compatibleServiceNamesFor(string $type): array
    {
        return match (Str::lower($type)) {
            'natacion', 'natación', 'swimming' => ['Natación', 'Natacion', 'Swimming'],
            'boxeo', 'boxing' => ['Boxeo', 'Boxing'],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'maximum_capacity' => 'integer',
            'requires_premium' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}

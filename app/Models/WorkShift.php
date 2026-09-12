<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\WorkShiftFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'start_time', 'end_time', 'is_active'])]
class WorkShift extends Model
{
    /** @use HasFactory<WorkShiftFactory> */
    use HasFactory;

    public function employeeShiftAssignments(): HasMany
    {
        return $this->hasMany(EmployeeShiftAssignment::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function spansMidnight(): bool
    {
        return Carbon::parse($this->end_time)->lessThan(Carbon::parse($this->start_time));
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

<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Factories\EmployeeShiftAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'work_shift_id', 'effective_from', 'effective_until', 'day_of_week', 'is_active'])]
class EmployeeShiftAssignment extends Model
{
    /** @use HasFactory<EmployeeShiftAssignmentFactory> */
    use HasFactory;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class);
    }

    public function scopeApplicableOn(Builder $query, CarbonInterface|string $date): Builder
    {
        $date = Carbon::parse($date);

        return $query
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $date)
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', $date);
            })
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('day_of_week')->orWhere('day_of_week', strtolower($date->englishDayOfWeek));
            });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
            'is_active' => 'boolean',
        ];
    }
}

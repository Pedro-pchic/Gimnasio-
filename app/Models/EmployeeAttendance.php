<?php

namespace App\Models;

use App\EmployeeAttendanceMethod;
use App\EmployeeAttendanceStatus;
use Carbon\Carbon;
use Database\Factories\EmployeeAttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'branch_id', 'work_shift_id', 'attendance_date', 'entry_time', 'exit_time', 'registration_method', 'status', 'observations'])]
class EmployeeAttendance extends Model
{
    /** @use HasFactory<EmployeeAttendanceFactory> */
    use HasFactory;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('exit_time');
    }

    public function workedMinutes(): ?int
    {
        if ($this->exit_time === null) {
            return null;
        }

        $date = $this->attendance_date->toDateString();
        $entry = Carbon::parse("{$date} {$this->entry_time}");
        $exit = Carbon::parse("{$date} {$this->exit_time}");

        if ($exit->lessThanOrEqualTo($entry)) {
            $exit->addDay();
        }

        return $entry->diffInMinutes($exit);
    }

    public function workedDuration(): ?string
    {
        $minutes = $this->workedMinutes();

        if ($minutes === null) {
            return null;
        }

        return sprintf('%d h %02d min', intdiv($minutes, 60), $minutes % 60);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'registration_method' => EmployeeAttendanceMethod::class,
            'status' => EmployeeAttendanceStatus::class,
        ];
    }
}

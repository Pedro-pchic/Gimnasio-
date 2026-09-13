<?php

namespace App\Models;

use App\EmployeeBonusStatus;
use Database\Factories\EmployeeBonusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'period', 'amount', 'reason', 'status', 'approved_by'])]
class EmployeeBonus extends Model
{
    /** @use HasFactory<EmployeeBonusFactory> */
    use HasFactory;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return array<string, string|class-string> */
    protected function casts(): array
    {
        return ['status' => EmployeeBonusStatus::class, 'amount' => 'decimal:2'];
    }
}

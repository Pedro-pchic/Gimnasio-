<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEmployeeShiftAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')],
            'work_shift_id' => ['required', 'integer', Rule::exists('work_shifts', 'id')],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date'],
            'day_of_week' => ['nullable', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['effective_from', 'effective_until'])) {
                    return;
                }

                if ($this->input('effective_until') !== null && $this->input('effective_until') < $this->input('effective_from')) {
                    $validator->errors()->add('effective_until', 'La fecha final debe ser igual o posterior a la fecha inicial.');
                }
            },
        ];
    }
}

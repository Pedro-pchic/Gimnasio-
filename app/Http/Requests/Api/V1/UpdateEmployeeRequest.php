<?php

namespace App\Http\Requests\Api\V1;

use App\EmployeeStatus;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEmployeeRequest extends FormRequest
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
        /** @var Employee $employee */
        $employee = $this->route('employee');

        return [
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')],
            'position_id' => ['required', 'integer', Rule::exists('positions', 'id')],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id'), Rule::unique('employees', 'user_id')->ignore($employee)],
            'code' => ['required', 'string', 'max:50', Rule::unique('employees', 'code')->ignore($employee)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'hired_at' => ['required', 'date'],
            'status' => ['required', Rule::enum(EmployeeStatus::class)],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('position_id')) {
                    return;
                }

                /** @var Employee $employee */
                $employee = $this->route('employee');
                $position = Position::query()->find($this->integer('position_id'));

                if (! $position?->is_active && $employee->position_id !== $position?->getKey()) {
                    $validator->errors()->add('position_id', 'El puesto seleccionado debe estar activo.');
                }
            },
        ];
    }
}

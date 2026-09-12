<?php

namespace App\Http\Requests\Api\V1;

use App\EmployeeStatus;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\GymClass;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreGymClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')],
            'instructor_employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'max:50'],
            'maximum_capacity' => ['required', 'integer', 'min:1'],
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
                if ($validator->errors()->hasAny(['branch_id', 'type'])) {
                    return;
                }

                $serviceNames = GymClass::compatibleServiceNamesFor($this->string('type')->toString());

                if ($serviceNames === [] || ! Service::query()->whereIn('name', $serviceNames)->exists()) {
                    return;
                }

                $hasCompatibleService = Branch::query()
                    ->findOrFail($this->integer('branch_id'))
                    ->services()
                    ->whereIn('name', $serviceNames)
                    ->exists();

                if (! $hasCompatibleService) {
                    $validator->errors()->add('branch_id', 'La sucursal no tiene el servicio compatible con esta actividad.');
                }
            },
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['branch_id', 'instructor_employee_id']) || $this->integer('instructor_employee_id') === 0) {
                    return;
                }

                $instructor = Employee::query()->with('position')->find($this->integer('instructor_employee_id'));

                if ($instructor === null) {
                    return;
                }

                if ($instructor->status !== EmployeeStatus::Active || ! $instructor->position->is_active || ! $instructor->position->can_teach) {
                    $validator->errors()->add('instructor_employee_id', 'El instructor seleccionado debe estar activo y habilitado para impartir clases.');
                }

                if ($instructor->branch_id !== $this->integer('branch_id')) {
                    $validator->errors()->add('instructor_employee_id', 'El instructor debe pertenecer a la sucursal de la actividad.');
                }
            },
        ];
    }
}

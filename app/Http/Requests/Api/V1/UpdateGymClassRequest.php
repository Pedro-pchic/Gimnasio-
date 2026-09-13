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

class UpdateGymClassRequest extends FormRequest
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
            'branch_id' => ['sometimes', 'required', 'integer', Rule::exists('branches', 'id')],
            'instructor_employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'required', 'string', 'max:50'],
            'maximum_capacity' => ['sometimes', 'required', 'integer', 'min:1'],
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

                /** @var GymClass $gymClass */
                $gymClass = $this->route('gym_class');
                $branchId = $this->integer('branch_id', $gymClass->branch_id);
                $type = $this->string('type', $gymClass->type)->toString();
                $serviceNames = GymClass::compatibleServiceNamesFor($type);

                if ($serviceNames === [] || ! Service::query()->whereIn('name', $serviceNames)->exists()) {
                    return;
                }

                $hasCompatibleService = Branch::query()
                    ->findOrFail($branchId)
                    ->services()
                    ->whereIn('name', $serviceNames)
                    ->exists();

                if (! $hasCompatibleService) {
                    $validator->errors()->add('branch_id', 'La sucursal no tiene el servicio compatible con esta actividad.');
                }
            },
            function (Validator $validator): void {
                if ($validator->errors()->has('instructor_employee_id') || $this->integer('instructor_employee_id') === 0) {
                    return;
                }

                /** @var GymClass $gymClass */
                $gymClass = $this->route('gym_class');
                $instructor = Employee::query()->with('position')->find($this->integer('instructor_employee_id'));

                if ($instructor === null) {
                    return;
                }

                if ($instructor->status !== EmployeeStatus::Active || ! $instructor->position->is_active || ! $instructor->position->can_teach) {
                    $validator->errors()->add('instructor_employee_id', 'El instructor seleccionado debe estar activo y habilitado para impartir clases.');
                }

                $branchId = $this->integer('branch_id', $gymClass->branch_id);

                if ($instructor->branch_id !== $branchId) {
                    $validator->errors()->add('instructor_employee_id', 'El instructor debe pertenecer a la sucursal de la actividad.');
                }
            },
        ];
    }
}

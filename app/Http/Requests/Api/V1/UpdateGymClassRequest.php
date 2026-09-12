<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Branch;
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
        ];
    }
}

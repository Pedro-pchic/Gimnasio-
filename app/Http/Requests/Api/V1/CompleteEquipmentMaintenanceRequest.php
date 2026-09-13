<?php

namespace App\Http\Requests\Api\V1;

use App\Models\EquipmentMaintenance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CompleteEquipmentMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'performed_date' => $this->input('performed_date') ?: now()->toDateString(),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'performed_date' => ['required', 'date'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('performed_date')) {
                    return;
                }

                /** @var EquipmentMaintenance $maintenance */
                $maintenance = $this->route('equipmentMaintenance');

                if ($maintenance->scheduled_date !== null && $this->date('performed_date')->isBefore($maintenance->scheduled_date)) {
                    $validator->errors()->add('performed_date', 'La fecha de realización no puede ser anterior a la fecha programada.');
                }
            },
        ];
    }
}

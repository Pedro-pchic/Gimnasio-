<?php

namespace App\Http\Requests\Api\V1;

use App\InventoryMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInventoryMovementRequest extends FormRequest
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
            'type' => ['required', Rule::enum(InventoryMovementType::class)],
            'quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['type', 'quantity'])) {
                    return;
                }

                if ($this->input('type') !== InventoryMovementType::Adjustment->value && $this->integer('quantity') === 0) {
                    $validator->errors()->add('quantity', 'La cantidad debe ser mayor que cero para entradas y salidas.');
                }
            },
        ];
    }
}

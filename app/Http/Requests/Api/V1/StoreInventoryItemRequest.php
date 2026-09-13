<?php

namespace App\Http\Requests\Api\V1;

use App\InventoryItemStatus;
use App\InventoryItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInventoryItemRequest extends FormRequest
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
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(InventoryItemType::class)],
            'internal_code' => ['required', 'string', 'max:255', Rule::unique('inventory_items', 'internal_code')],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(InventoryItemStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'acquisition_date' => ['nullable', 'date'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial' => ['nullable', 'string', 'max:255'],
            'maintenance_required' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['type', 'quantity', 'minimum_stock'])) {
                    return;
                }

                if ($this->input('type') === InventoryItemType::Equipment->value && blank($this->input('description'))) {
                    $validator->errors()->add('description', 'La descripción es obligatoria para los equipos.');
                }

                if ($this->filled('minimum_stock') && ! $this->filled('quantity')) {
                    $validator->errors()->add('quantity', 'Debe indicar cantidad cuando define un stock mínimo.');
                }
            },
        ];
    }
}

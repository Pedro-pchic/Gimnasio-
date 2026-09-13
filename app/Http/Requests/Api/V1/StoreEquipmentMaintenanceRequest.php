<?php

namespace App\Http\Requests\Api\V1;

use App\EquipmentMaintenanceStatus;
use App\InventoryItemType;
use App\Models\InventoryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEquipmentMaintenanceRequest extends FormRequest
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
            'inventory_item_id' => ['required', 'integer', Rule::exists('inventory_items', 'id')],
            'scheduled_date' => ['nullable', 'date'],
            'performed_date' => ['nullable', 'date'],
            'description' => ['required', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(EquipmentMaintenanceStatus::class)],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['inventory_item_id', 'scheduled_date', 'performed_date', 'status'])) {
                    return;
                }

                $inventoryItem = InventoryItem::query()->find($this->integer('inventory_item_id'));

                if ($inventoryItem?->type !== InventoryItemType::Equipment) {
                    $validator->errors()->add('inventory_item_id', 'El mantenimiento solo puede registrarse para equipos.');
                }

                if ($this->input('status') === EquipmentMaintenanceStatus::Completed->value && ! $this->filled('performed_date')) {
                    $validator->errors()->add('performed_date', 'Debe indicar la fecha de realización para completar el mantenimiento.');
                }

                if ($this->input('status') !== EquipmentMaintenanceStatus::Completed->value && $this->filled('performed_date')) {
                    $validator->errors()->add('performed_date', 'La fecha de realización solo aplica a mantenimientos completados.');
                }

                if ($this->filled('scheduled_date') && $this->filled('performed_date') && $this->date('performed_date')->isBefore($this->date('scheduled_date'))) {
                    $validator->errors()->add('performed_date', 'La fecha de realización no puede ser anterior a la fecha programada.');
                }
            },
        ];
    }
}

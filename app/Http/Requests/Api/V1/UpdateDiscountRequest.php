<?php

namespace App\Http\Requests\Api\V1;

use App\DiscountType;
use App\Models\Discount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateDiscountRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', Rule::enum(DiscountType::class)],
            'value' => ['sometimes', 'required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
            'third_party_item_ids' => ['sometimes', 'required', 'array', 'min:1'],
            'third_party_item_ids.*' => ['integer', 'distinct', Rule::exists('third_party_items', 'id')],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer', 'distinct', Rule::exists('branches', 'id')],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Discount $discount */
                $discount = $this->route('discount');
                $type = $this->input('type', $discount->type->value);
                $value = (float) $this->input('value', $discount->value);

                if ($type === DiscountType::Percentage->value && $value > 100) {
                    $validator->errors()->add('value', 'El descuento porcentual no puede superar el 100%.');
                }
            },
        ];
    }
}

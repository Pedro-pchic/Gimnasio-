<?php

namespace App\Http\Requests\Api\V1;

use App\PaymentMethod;
use App\SaleDetailType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSaleRequest extends FormRequest
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
            'client_id' => ['nullable', 'integer', Rule::exists('clients', 'id')],
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')],
            'sale_date' => ['required', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:255'],
            'observations' => ['nullable', 'string'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.concept_type' => ['required', Rule::enum(SaleDetailType::class)],
            'details.*.concept_reference_id' => ['nullable', 'integer', 'min:1'],
            'details.*.description' => ['required', 'string', 'max:255'],
            'details.*.quantity' => ['required', 'numeric', 'gt:0'],
            'details.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['discount', 'details'])) {
                    return;
                }

                $details = $this->input('details', []);

                if (! is_array($details)) {
                    return;
                }

                $subtotal = collect($details)->sum(function (mixed $detail): float {
                    if (! is_array($detail)) {
                        return 0;
                    }

                    return (float) ($detail['quantity'] ?? 0) * (float) ($detail['unit_price'] ?? 0);
                });

                if ((float) $this->input('discount', 0) > $subtotal) {
                    $validator->errors()->add('discount', 'El descuento no puede superar el subtotal de la venta.');
                }
            },
        ];
    }
}

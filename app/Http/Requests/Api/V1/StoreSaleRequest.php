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
            'referral_credit_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:255'],
            'observations' => ['nullable', 'string'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.concept_type' => ['required', Rule::enum(SaleDetailType::class)],
            'details.*.concept_reference_id' => ['nullable', 'integer', 'min:1'],
            'details.*.description' => ['nullable', 'string', 'max:255'],
            'details.*.quantity' => ['required', 'numeric', 'gt:0'],
            'details.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $validator->errors()->hasAny(['client_id', 'referral_credit_amount'])
                    && (float) $this->input('referral_credit_amount', 0) > 0
                    && blank($this->input('client_id'))) {
                    $validator->errors()->add('client_id', 'Selecciona el cliente que utilizará el crédito de referido.');
                }

                if ($validator->errors()->has('details')) {
                    return;
                }

                $details = $this->input('details', []);

                if (! is_array($details)) {
                    return;
                }

                foreach ($details as $index => $detail) {
                    if (! is_array($detail)) {
                        continue;
                    }

                    $conceptType = $detail['concept_type'] ?? null;

                    if (in_array($conceptType, SaleDetailType::thirdPartyValues(), true)) {
                        if (empty($detail['concept_reference_id'])) {
                            $validator->errors()->add("details.{$index}.concept_reference_id", 'Selecciona un producto o servicio externo.');
                        }

                        continue;
                    }

                    if (blank($detail['description'] ?? null)) {
                        $validator->errors()->add("details.{$index}.description", 'La descripción es obligatoria para los conceptos manuales.');
                    }

                    if (! array_key_exists('unit_price', $detail) || $detail['unit_price'] === null || $detail['unit_price'] === '') {
                        $validator->errors()->add("details.{$index}.unit_price", 'El precio unitario es obligatorio para los conceptos manuales.');
                    }
                }
            },
        ];
    }
}

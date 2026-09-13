<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQualityCertificateRequest extends FormRequest
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
            'purchase_order_id' => ['required', 'integer', Rule::exists('purchase_orders', 'id')],
            'number' => ['required', 'string', 'max:255', Rule::unique('quality_certificates', 'number')],
            'issued_date' => ['nullable', 'date'],
            'expires_date' => ['nullable', 'date', 'after_or_equal:issued_date'],
            'status' => ['required', Rule::in(['pending', 'valid', 'expired'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

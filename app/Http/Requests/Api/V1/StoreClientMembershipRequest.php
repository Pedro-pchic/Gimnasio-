<?php

namespace App\Http\Requests\Api\V1;

use App\ClientMembershipStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientMembershipRequest extends FormRequest
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
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
            'membership_type_id' => ['required', 'integer', Rule::exists('membership_types', 'id')],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'applied_price' => ['required', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::enum(ClientMembershipStatus::class)],
            'observations' => ['nullable', 'string'],
        ];
    }
}

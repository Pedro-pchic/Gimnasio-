<?php

namespace App\Http\Requests\Api\V1;

use App\ClientMembershipStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateClientMembershipRequest extends FormRequest
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
            'client_id' => ['sometimes', 'required', 'integer', Rule::exists('clients', 'id')],
            'membership_type_id' => ['sometimes', 'required', 'integer', Rule::exists('membership_types', 'id')],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after_or_equal:start_date'],
            'applied_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::enum(ClientMembershipStatus::class)],
            'observations' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['start_date', 'end_date'])) {
                    return;
                }

                $membership = $this->route('client_membership');
                $startDate = $this->input('start_date', $membership->start_date?->toDateString());
                $endDate = $this->input('end_date', $membership->end_date?->toDateString());

                if ($startDate !== null && $endDate !== null && $endDate < $startDate) {
                    $validator->errors()->add('end_date', 'The end date must be after or equal to the start date.');
                }
            },
        ];
    }
}

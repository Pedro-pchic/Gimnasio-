<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Referral;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreReferralRequest extends FormRequest
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
            'referrer_client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
            'referred_client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['referrer_client_id', 'referred_client_id'])) {
                    return;
                }

                $referrerClientId = (int) $this->input('referrer_client_id');
                $referredClientId = (int) $this->input('referred_client_id');

                if ($referrerClientId === $referredClientId) {
                    $validator->errors()->add('referred_client_id', 'Un cliente no puede referirse a sí mismo.');

                    return;
                }

                if (Referral::query()->where('referred_client_id', $referredClientId)->exists()) {
                    $validator->errors()->add('referred_client_id', 'El cliente ya tiene un referidor registrado.');
                }
            },
        ];
    }
}

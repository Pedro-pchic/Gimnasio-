<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends FormRequest
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
        /** @var Position $position */
        $position = $this->route('position');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('positions', 'name')->ignore($position)],
            'description' => ['nullable', 'string'],
            'can_teach' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

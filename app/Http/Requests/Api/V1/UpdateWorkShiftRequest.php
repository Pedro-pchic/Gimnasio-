<?php

namespace App\Http\Requests\Api\V1;

use App\Models\WorkShift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkShiftRequest extends FormRequest
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
        /** @var WorkShift $workShift */
        $workShift = $this->route('work_shift');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('work_shifts', 'name')->ignore($workShift)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

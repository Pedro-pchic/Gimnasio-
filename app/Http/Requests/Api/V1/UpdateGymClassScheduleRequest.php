<?php

namespace App\Http\Requests\Api\V1;

use App\Models\GymClassSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateGymClassScheduleRequest extends FormRequest
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
            'gym_class_id' => ['sometimes', 'required', 'integer', Rule::exists('gym_classes', 'id')],
            'day_of_week' => ['sometimes', 'required', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i'],
            'maximum_capacity' => ['sometimes', 'required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['start_time', 'end_time'])) {
                    return;
                }

                /** @var GymClassSchedule $schedule */
                $schedule = $this->route('class_schedule');
                $startTime = $this->input('start_time', $schedule->start_time);
                $endTime = $this->input('end_time', $schedule->end_time);

                if ($endTime <= $startTime) {
                    $validator->errors()->add('end_time', 'La hora de fin debe ser posterior a la hora de inicio.');
                }
            },
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'slots' => ['required', 'array', 'min:1'],
            'slots.*.start_time' => ['required', 'date_format:H:i'],
            'slots.*.end_time' => ['required', 'date_format:H:i', 'after:slots.*.start_time'],
            'slots.*.is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slots.*.end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ];
    }
}
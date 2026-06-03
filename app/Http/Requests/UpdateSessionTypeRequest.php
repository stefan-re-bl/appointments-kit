<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSessionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Ignorar el modelo actual al validar unicidad
                Rule::unique('session_types')->ignore($this->route('session_type'))->where(function ($query) use ($user) {
                    return $query->where('therapist_id', $user->therapist->id);
                }),
            ],
            'duration_minutes' => ['required', 'integer', 'in:30,60,90'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'in:ARS,USD'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
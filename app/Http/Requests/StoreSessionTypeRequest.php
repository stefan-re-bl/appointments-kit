<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSessionTypeRequest extends FormRequest
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
                // Nombre único por terapeuta
                Rule::unique('session_types')->where(function ($query) use ($user) {
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
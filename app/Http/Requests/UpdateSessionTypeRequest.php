<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\SessionType;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSessionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        $sessionType = $this->route('session_type');

        return $user instanceof User
            && $user->therapist !== null
            && $sessionType instanceof SessionType
            && (int) $sessionType->therapist_id === (int) $user->therapist->id;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        $sessionType = $this->route('session_type');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('session_types')
                    ->ignore($sessionType)
                    ->where(fn ($query) => $query->where('therapist_id', $user->therapist->id)),
            ],
            'duration_minutes' => ['required', 'integer', 'in:30,60,90'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'in:ARS,USD'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
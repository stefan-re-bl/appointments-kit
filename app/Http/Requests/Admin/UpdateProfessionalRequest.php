<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use App\Models\Professional;
use App\Models\User;
use App\Rules\ValidTimezone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProfessionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        if ($user->role instanceof Role) {
            return $user->role === Role::ADMIN;
        }

        return $user->role === Role::ADMIN->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $professional = $this->route('professional');

        $professionalUserId = $professional instanceof Professional
            ? $professional->user_id
            : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($professionalUserId),
            ],
            'timezone' => ['required', 'string', new ValidTimezone],
            'google_meet_link' => ['nullable', 'url', 'max:2048'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'avatar_url' => ['nullable', 'url', 'max:2048'],
            'session_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'session_currency' => ['required', 'string', Rule::in(config('booking.currencies.supported', ['ARS', 'USD']))],
            'is_active' => ['sometimes', 'boolean'],
            'is_approved' => ['sometimes', 'boolean'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ContactMessageStatus;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateContactMessageStatusRequest extends FormRequest
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
        return [
            'status' => ['required', Rule::enum(ContactMessageStatus::class)],
        ];
    }
}

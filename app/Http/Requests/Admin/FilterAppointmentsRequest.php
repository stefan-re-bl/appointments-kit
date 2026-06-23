<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class FilterAppointmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User|null $user */
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
            'therapist_id' => ['nullable', 'integer', Rule::exists('therapists', 'id')],
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'payment_status' => ['nullable', Rule::enum(PaymentStatus::class)],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ];
    }
}
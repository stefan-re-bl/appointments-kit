<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class FilterAppointmentsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $professionalId = $this->input('professional_id', $this->input('professional_id'));

        $this->merge([
            'professional_id' => $professionalId,
        ]);
    }

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
            'professional_id' => ['nullable', 'integer', Rule::exists('professionals', 'id')],
            'professional_id' => ['nullable', 'integer', Rule::exists('professionals', 'id')],
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'payment_status' => ['nullable', Rule::enum(PaymentStatus::class)],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ];
    }
}

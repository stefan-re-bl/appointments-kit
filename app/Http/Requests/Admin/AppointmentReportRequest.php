<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AppointmentReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        return $user->role === Role::ADMIN || $user->role === Role::ADMIN->value;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'professional_id' => ['nullable', 'integer', Rule::exists('professionals', 'id')],
        ];
    }

    /**
     * @return array{date_from: string|null, date_to: string|null, professional_id: int|null}
     */
    public function filters(): array
    {
        $professionalId = $this->input('professional_id', $this->input('professional_id'));

        return [
            'date_from' => $this->filled('date_from') ? (string) $this->input('date_from') : null,
            'date_to' => $this->filled('date_to') ? (string) $this->input('date_to') : null,
            'professional_id' => is_numeric($professionalId) ? (int) $professionalId : null,
        ];
    }
}

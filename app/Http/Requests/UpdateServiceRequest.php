<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        $service = $this->route('service');

        return $user instanceof User
            && $user->professional !== null
            && $service instanceof Service
            && (int) $service->professional_id === (int) $user->professional->id;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        $service = $this->route('service');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('session_types')
                    ->ignore($service)
                    ->where(fn ($query) => $query->where('professional_id', $user->professional->id)),
            ],
            'duration_minutes' => ['required', 'integer', 'in:30,60,90'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', Rule::in(config('booking.currencies.supported', ['ARS', 'USD']))],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('app.session_type_management.fields.name'),
            'duration_minutes' => __('app.session_type_management.fields.duration_minutes'),
            'price' => __('app.session_type_management.fields.price'),
            'currency' => __('app.session_type_management.fields.currency'),
            'is_active' => __('app.session_type_management.fields.is_active'),
        ];
    }
}

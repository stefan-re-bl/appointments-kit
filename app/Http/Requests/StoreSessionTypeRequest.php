<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSessionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        return $user instanceof User && $user->professional !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('session_types')->where(
                    fn ($query) => $query->where('professional_id', $user->professional->id)
                ),
            ],
            'duration_minutes' => ['required', 'integer', 'in:30,60,90'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'in:ARS,USD'],
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

<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\CountryTimezoneService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $therapistCountry = $this->input('therapist_country');
        $therapistTimezone = $this->input('therapist_timezone');

        $this->merge([
            'therapist_country' => is_string($therapistCountry) ? strtoupper(trim($therapistCountry)) : $therapistCountry,
            'therapist_timezone' => is_string($therapistTimezone) && trim($therapistTimezone) !== ''
                ? trim($therapistTimezone)
                : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $countryTimezoneService = app(CountryTimezoneService::class);
        $therapistCountry = is_string($this->input('therapist_country'))
            ? strtoupper(trim($this->input('therapist_country')))
            : null;
        $timezoneReferenceDate = now('UTC')->toDateString();
        $timezoneOptions = $countryTimezoneService->timezoneOptionsForCountryOnDate(
            $therapistCountry,
            $timezoneReferenceDate,
        );
        $hasTherapistProfile = $this->user()?->therapist !== null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'bio' => ['nullable', 'string', 'max:5000'],
            'specialties' => ['nullable', 'string', 'max:3000'],
            'therapeutic_approach' => ['nullable', 'string', 'max:3000'],
            'payment_instructions' => ['nullable', 'string', 'max:3000'],
            'google_meet_link' => ['nullable', 'url', 'max:2048'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'presentation_video' => [
                'nullable',
                'file',
                'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime',
                'max:51200',
            ],
            'avatar_url' => [
                'nullable',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
                        return;
                    }

                    if (str_starts_with($value, '/storage/therapists/avatars/')) {
                        return;
                    }

                    $fail(__('validation.url', ['attribute' => $attribute]));
                },
            ],
            'therapist_country' => [
                Rule::requiredIf($hasTherapistProfile),
                'nullable',
                'string',
                Rule::in(array_keys($countryTimezoneService->countries())),
            ],
            'therapist_timezone' => [
                Rule::requiredIf(
                    fn (): bool => $hasTherapistProfile
                        && $countryTimezoneService->regionIsRequired($therapistCountry, $timezoneReferenceDate),
                ),
                'nullable',
                'string',
                Rule::in(array_keys($timezoneOptions)),
            ],
        ];
    }
}

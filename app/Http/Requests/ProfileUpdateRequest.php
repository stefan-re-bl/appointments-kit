<?php

namespace App\Http\Requests;

use App\Enums\SupportedLocale;
use App\Models\User;
use App\Services\CountryTimezoneService;
use App\Services\Notifications\PhoneNumberNormalizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $professionalCountry = $this->input('professional_country', $this->input('therapist_country'));
        $professionalTimezone = $this->input('professional_timezone', $this->input('therapist_timezone'));
        $professionalApproach = $this->input('professional_approach', $this->input('therapeutic_approach'));

        $this->merge([
            'professional_country' => is_string($professionalCountry) ? strtoupper(trim($professionalCountry)) : $professionalCountry,
            'professional_timezone' => is_string($professionalTimezone) && trim($professionalTimezone) !== ''
                ? trim($professionalTimezone)
                : null,
            'professional_approach' => $professionalApproach,
            'preferred_locale' => SupportedLocale::normalize($this->input('preferred_locale')),
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
        $professionalCountry = is_string($this->input('professional_country'))
            ? strtoupper(trim($this->input('professional_country')))
            : null;
        $timezoneReferenceDate = now('UTC')->toDateString();
        $timezoneOptions = $countryTimezoneService->timezoneOptionsForCountryOnDate(
            $professionalCountry,
            $timezoneReferenceDate,
        );
        $hasProfessionalProfile = $this->user()?->professional !== null;

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
            'professional_approach' => ['nullable', 'string', 'max:3000'],
            'payment_instructions' => ['nullable', 'string', 'max:3000'],
            'google_meet_link' => ['nullable', 'url', 'max:2048'],
            'whatsapp_phone' => [
                'nullable',
                'string',
                'max:32',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if (app(PhoneNumberNormalizer::class)->normalize((string) $value) === null) {
                        $fail(__('app.whatsapp.validation_phone'));
                    }
                },
            ],
            'whatsapp_notifications_enabled' => ['nullable', 'boolean'],
            'whatsapp_confirmations_enabled' => ['nullable', 'boolean'],
            'whatsapp_reminders_enabled' => ['nullable', 'boolean'],
            'preferred_locale' => [
                Rule::requiredIf($hasProfessionalProfile),
                'nullable',
                'string',
                Rule::in(SupportedLocale::values()),
            ],
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

                    if (
                        str_starts_with($value, '/storage/professionals/avatars/')
                        || str_starts_with($value, '/storage/professionals/avatars/')
                    ) {
                        return;
                    }

                    $fail(__('validation.url', ['attribute' => $attribute]));
                },
            ],
            'professional_country' => [
                Rule::requiredIf($hasProfessionalProfile),
                'nullable',
                'string',
                Rule::in(array_keys($countryTimezoneService->countries())),
            ],
            'professional_timezone' => [
                Rule::requiredIf(
                    fn (): bool => $hasProfessionalProfile
                        && $countryTimezoneService->regionIsRequired($professionalCountry, $timezoneReferenceDate),
                ),
                'nullable',
                'string',
                Rule::in(array_keys($timezoneOptions)),
            ],
        ];
    }
}

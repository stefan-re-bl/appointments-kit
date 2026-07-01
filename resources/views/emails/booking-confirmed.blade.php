<x-mail::message>
# {{ __('app.emails.booking_confirmed.title') }}

@if ($recipientType === \App\Mail\BookingConfirmed::RECIPIENT_PATIENT)
{{ __('app.emails.booking_confirmed.greeting_patient', ['name' => $patientName]) }}
@else
{{ __('app.emails.booking_confirmed.greeting_therapist', ['name' => $therapistName]) }}
@endif

{{ __('app.emails.booking_confirmed.intro') }}

<x-mail::panel>
<strong>{{ __('app.emails.booking_confirmed.patient') }}:</strong> {{ $patientName }}<br>
<strong>{{ __('app.emails.booking_confirmed.therapist') }}:</strong> {{ $therapistName }}<br>
<strong>{{ __('app.emails.booking_confirmed.session_type') }}:</strong> {{ $sessionTypeName }}<br>
<strong>{{ __('app.emails.booking_confirmed.starts_at') }}:</strong> {{ $startsAt }}<br>
<strong>{{ __('app.emails.booking_confirmed.ends_at') }}:</strong> {{ $endsAt }}<br>
<strong>{{ __('app.emails.booking_confirmed.timezone') }}:</strong> {{ $displayTimezone }}
</x-mail::panel>

@if ($recipientType === \App\Mail\BookingConfirmed::RECIPIENT_PATIENT)
{{ __('app.emails.booking_confirmed.payment_patient_notice') }}

@if (filled($paymentInstructions))
<x-mail::panel>
<strong>{{ __('app.emails.booking_confirmed.payment_instructions') }}</strong><br>
{{ $paymentInstructions }}
</x-mail::panel>
@endif

{{ __('legal.email.patient_disclaimer') }}
@else
{{ __('app.emails.booking_confirmed.payment_therapist_notice') }}
@endif

<x-mail::button :url="$appointmentUrl">
{{ __('app.emails.booking_confirmed.my_appointment_button') }}
</x-mail::button>

@if ($recipientType === \App\Mail\BookingConfirmed::RECIPIENT_PATIENT)
<x-mail.reschedule-link :appointment="$appointment" />
@endif

@if ($meetLink)
<x-mail::button :url="$meetLink">
{{ __('app.emails.booking_confirmed.meet_button') }}
</x-mail::button>
@endif

{{ __('app.emails.booking_confirmed.footer') }}

[{{ __('legal.nav.terms') }}]({{ route('legal.terms') }}) · [{{ __('legal.nav.privacy') }}]({{ route('legal.privacy') }}) · [{{ __('legal.nav.emergency') }}]({{ route('legal.emergency-notice') }})

{{ config('app.name') }}
</x-mail::message>

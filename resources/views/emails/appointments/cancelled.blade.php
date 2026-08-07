<x-mail::message>
# {{ __('appointment_emails.cancelled.title') }}

{{ __('appointment_emails.common.greeting', ['name' => $recipientName]) }}

{{ __('appointment_emails.cancelled.intro') }}

<x-mail::panel>
**{{ __('appointment_emails.common.patient') }}:** {{ $patientName }}

**{{ __('appointment_emails.common.therapist') }}:** {{ $therapistName }}

**{{ __('appointment_emails.cancelled.appointment_time') }}:** {{ $appointmentRange }}

**{{ __('appointment_emails.common.timezone') }}:** {{ $recipientTimezone }}
</x-mail::panel>

<x-mail::button :url="$publicUrl">
{{ __('appointment_emails.common.view_appointment') }}
</x-mail::button>

{{ __('appointment_emails.cancelled.footer') }}

{{ __('appointment_emails.common.thanks') }},  
{{ config('app.name') }}
</x-mail::message>

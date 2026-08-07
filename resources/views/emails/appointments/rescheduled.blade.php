<x-mail::message>
# {{ __('appointment_emails.rescheduled.title') }}

{{ __('appointment_emails.common.greeting', ['name' => $recipientName]) }}

{{ __('appointment_emails.rescheduled.intro') }}

<x-mail::panel>
**{{ __('appointment_emails.common.patient') }}:** {{ $patientName }}

**{{ __('appointment_emails.common.therapist') }}:** {{ $therapistName }}

**{{ __('appointment_emails.rescheduled.previous_time') }}:** {{ $previousRange }}

**{{ __('appointment_emails.rescheduled.new_time') }}:** {{ $newRange }}

**{{ __('appointment_emails.common.timezone') }}:** {{ $recipientTimezone }}
</x-mail::panel>

<x-mail::button :url="$publicUrl">
{{ __('appointment_emails.common.view_appointment') }}
</x-mail::button>

<x-mail.reschedule-link :appointment="$appointment" />

{{ __('appointment_emails.rescheduled.footer') }}

{{ __('appointment_emails.common.thanks') }},  
{{ config('app.name') }}
</x-mail::message>

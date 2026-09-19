<x-mail::message>
# {{ __('reminders.appointment.greeting', ['name' => $appointment->patient_name]) }}

{{ __('reminders.appointment.intro') }}

<x-mail::panel>
**{{ __('reminders.appointment.professional') }}:** {{ $appointment->professional->user->name }}

**{{ __('reminders.appointment.starts_at') }}:** {{ $startsAt }}

**{{ __('reminders.appointment.ends_at') }}:** {{ $endsAt }}

**{{ __('reminders.appointment.timezone') }}:** {{ $timezone }}
</x-mail::panel>

<x-mail::button :url="$publicUrl">
{{ __('reminders.appointment.public_button') }}
</x-mail::button>

<x-mail.reschedule-link :appointment="$appointment" />

@if ($googleMeetLink)
<x-mail::button :url="$googleMeetLink">
{{ __('reminders.appointment.meet_button') }}
</x-mail::button>
@endif

{{ __('reminders.appointment.footer') }}

{{ __('reminders.appointment.salutation') }}
</x-mail::message>

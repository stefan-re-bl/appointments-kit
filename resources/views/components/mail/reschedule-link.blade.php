@props([
    'appointment',
])

@php
    $canReschedule = app(\App\Services\CancellationPolicyService::class)->canReschedule($appointment);
    $rescheduleUrl = $canReschedule
        ? app(\App\Services\AppointmentSignedUrlService::class)->rescheduleUrl($appointment)
        : null;
@endphp

@if ($rescheduleUrl !== null)
<x-mail::button :url="$rescheduleUrl">
{{ __('appointment_emails.reschedule.action') }}
</x-mail::button>

{{ __('appointment_emails.reschedule.signed_url_notice') }}
@endif
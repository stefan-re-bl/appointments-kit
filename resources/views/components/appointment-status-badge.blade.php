@props([
    'status',
])

@php
    $value = $status instanceof \App\Enums\AppointmentStatus
        ? $status->value
        : (string) $status;

    $classes = match ($value) {
        \App\Enums\AppointmentStatus::CONFIRMED->value => 'inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-800',
        \App\Enums\AppointmentStatus::PENDING->value => 'inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-800',
        \App\Enums\AppointmentStatus::CANCELLED->value => 'inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-800',
        \App\Enums\AppointmentStatus::COMPLETED->value => 'inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-800',
        default => 'inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-xs font-medium text-slate-700',
    };
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ __('app.appointment_status.' . $value) }}
</span>

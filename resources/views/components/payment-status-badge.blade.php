@props([
    'status',
])

@php
    $value = $status instanceof \App\Enums\PaymentStatus
        ? $status->value
        : (string) $status;

    $classes = match ($value) {
        \App\Enums\PaymentStatus::PAID->value => 'inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800',
        \App\Enums\PaymentStatus::WAIVED->value => 'inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800',
        default => 'inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800',
    };
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ __('app.payments.status.' . $value) }}
</span>
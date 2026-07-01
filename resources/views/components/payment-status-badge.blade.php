@props([
    'status',
])

@php
    $value = $status instanceof \App\Enums\PaymentStatus
        ? $status->value
        : (string) $status;

    $classes = match ($value) {
        \App\Enums\PaymentStatus::PAID->value => 'inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-800',
        \App\Enums\PaymentStatus::WAIVED->value => 'inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-xs font-medium text-slate-700',
        default => 'inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-800',
    };
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ __('app.payments.status.' . $value) }}
</span>

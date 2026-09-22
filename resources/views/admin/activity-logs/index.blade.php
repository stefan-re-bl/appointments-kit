@extends('layouts.admin', [
    'title' => __('app.admin.activity_logs.title'),
    'header' => __('app.admin.activity_logs.title'),
])

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-brand-title">
                {{ __('app.admin.activity_logs.title') }}
            </h2>
            <p class="mt-1 text-sm text-slate-600">
                {{ __('app.admin.activity_logs.description') }}
            </p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('app.admin.activity_logs.created_at') }}</th>
                            <th class="px-4 py-3">{{ __('app.admin.activity_logs.event') }}</th>
                            <th class="px-4 py-3">{{ __('app.admin.activity_logs.appointment') }}</th>
                            <th class="px-4 py-3">{{ __('app.admin.activity_logs.causer') }}</th>
                            <th class="px-4 py-3">{{ __('app.admin.activity_logs.old_values') }}</th>
                            <th class="px-4 py-3">{{ __('app.admin.activity_logs.new_values') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($logs as $log)
                            @php
                                $valueClasses = function (string $field, mixed $value, bool $isNew): string {
                                    $base = 'inline-flex max-w-xs rounded-lg px-2.5 py-1 text-xs font-medium ring-1 ';
                                    $value = (string) $value;

                                    if ($field === 'status') {
                                        return $base . match ($value) {
                                            \App\Enums\AppointmentStatus::CONFIRMED->value => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
                                            \App\Enums\AppointmentStatus::PENDING->value => 'bg-amber-50 text-amber-800 ring-amber-100',
                                            \App\Enums\AppointmentStatus::CANCELLED->value => 'bg-rose-50 text-rose-800 ring-rose-100',
                                            \App\Enums\AppointmentStatus::COMPLETED->value => 'bg-sky-50 text-sky-800 ring-sky-100',
                                            default => 'bg-slate-100 text-slate-800 ring-slate-200',
                                        };
                                    }

                                    if ($field === 'payment_status') {
                                        return $base . match ($value) {
                                            \App\Enums\PaymentStatus::PAID->value => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
                                            \App\Enums\PaymentStatus::PENDING->value => 'bg-amber-50 text-amber-800 ring-amber-100',
                                            \App\Enums\PaymentStatus::WAIVED->value => 'bg-slate-100 text-slate-800 ring-slate-200',
                                            default => 'bg-slate-100 text-slate-800 ring-slate-200',
                                        };
                                    }

                                    if ($value === '') {
                                        return $base . 'bg-slate-100 text-slate-600 ring-slate-200';
                                    }

                                    return $base . ($isNew
                                        ? 'bg-brand-accent-soft text-brand-title ring-brand-accent-soft'
                                        : 'bg-slate-100 text-slate-800 ring-slate-200');
                                };
                            @endphp
                            <tr class="align-top">
                                <td class="whitespace-nowrap px-4 py-4 text-slate-700">
                                    {{ $timezoneService->formatForDisplay($log->created_at, 'd/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full bg-brand-accent-soft px-3 py-1 text-xs font-semibold text-brand-title ring-1 ring-brand-accent-soft">
                                        {{ __('app.admin.activity_logs.events.' . $log->event) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-slate-700">
                                    <div class="font-medium text-slate-950">
                                        #{{ $log->appointment_id }}
                                    </div>
                                    <div>{{ $log->appointment?->customer_name }}</div>
                                    <div class="text-xs text-slate-500">
                                        {{ $log->appointment?->professional?->user?->name }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-slate-700">
                                    {{ $log->causer?->name ?? __('app.admin.activity_logs.system') }}
                                </td>
                                <td class="px-4 py-4">
                                    <dl class="space-y-2">
                                        @foreach ($log->old_values ?? [] as $field => $value)
                                            <div>
                                                <dt class="text-xs font-medium text-slate-500">
                                                    {{ __('app.admin.activity_logs.fields.' . $field) }}
                                                </dt>
                                                <dd class="mt-1">
                                                    <span @class($valueClasses((string) $field, $value, false))>
                                                        @if ($field === 'status' && filled($value))
                                                            {{ __('app.appointment_public.status.' . $value) }}
                                                        @elseif ($field === 'payment_status' && filled($value))
                                                            {{ __('app.payment_status.' . $value) }}
                                                        @else
                                                            {{ $value ?? __('app.admin.activity_logs.empty_value') }}
                                                        @endif
                                                    </span>
                                                </dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </td>
                                <td class="px-4 py-4">
                                    <dl class="space-y-2">
                                        @foreach ($log->new_values ?? [] as $field => $value)
                                            <div>
                                                <dt class="text-xs font-medium text-slate-500">
                                                    {{ __('app.admin.activity_logs.fields.' . $field) }}
                                                </dt>
                                                <dd class="mt-1">
                                                    <span @class($valueClasses((string) $field, $value, true))>
                                                        @if ($field === 'status' && filled($value))
                                                            {{ __('app.appointment_public.status.' . $value) }}
                                                        @elseif ($field === 'payment_status' && filled($value))
                                                            {{ __('app.payment_status.' . $value) }}
                                                        @else
                                                            {{ $value ?? __('app.admin.activity_logs.empty_value') }}
                                                        @endif
                                                    </span>
                                                </dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                                    {{ __('app.admin.activity_logs.empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

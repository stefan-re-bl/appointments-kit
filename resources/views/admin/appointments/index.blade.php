@extends('layouts.admin', [
    'title' => __('app.admin.appointments.title'),
    'header' => __('app.admin.appointments.title'),
])

@section('content')
    <div
        x-data="{
            refreshTimer: null,
            submitTimer: null,
            submitFilters() {
                clearTimeout(this.submitTimer);
                this.submitTimer = setTimeout(() => this.$refs.filters.submit(), 300);
            },
            resetFilters() {
                window.location.href = '{{ route('admin.appointments.index') }}';
            },
            init() {
                this.refreshTimer = setInterval(() => {
                    if (! document.hidden) {
                        window.location.reload();
                    }
                }, 60000);
            }
        }"
        x-init="init()"
        class="space-y-6"
    >
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('app.admin.appointments.summary.total') }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ $summary['total'] }}</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('app.admin.appointments.summary.confirmed') }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ $summary['confirmed'] }}</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('app.admin.appointments.summary.pending_payment') }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ $summary['pending_payment'] }}</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('app.admin.appointments.summary.today') }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ $summary['today'] }}</p>
            </article>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form x-ref="filters" method="GET" action="{{ route('admin.appointments.index') }}">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div>
                        <label for="therapist_id" class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('app.admin.appointments.filters.therapist') }}
                        </label>
                        <select
                            id="therapist_id"
                            name="therapist_id"
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-umbralia-accent focus:ring-umbralia-accent/30"
                            @change="submitFilters()"
                        >
                            <option value="">{{ __('app.admin.appointments.filters.all_therapists') }}</option>
                            @foreach ($therapists as $therapist)
                                <option
                                    value="{{ $therapist->id }}"
                                    @selected((string) ($filters['therapist_id'] ?? '') === (string) $therapist->id)
                                >
                                    {{ $therapist->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('app.admin.appointments.filters.status') }}
                        </label>
                        <select
                            id="status"
                            name="status"
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-umbralia-accent focus:ring-umbralia-accent/30"
                            @change="submitFilters()"
                        >
                            <option value="">{{ __('app.admin.appointments.filters.all_statuses') }}</option>
                            @foreach ($appointmentStatuses as $status)
                                <option
                                    value="{{ $status->value }}"
                                    @selected(($filters['status'] ?? '') === $status->value)
                                >
                                    {{ __('app.appointment_status.' . $status->value) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="payment_status" class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('app.admin.appointments.filters.payment_status') }}
                        </label>
                        <select
                            id="payment_status"
                            name="payment_status"
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-umbralia-accent focus:ring-umbralia-accent/30"
                            @change="submitFilters()"
                        >
                            <option value="">{{ __('app.admin.appointments.filters.all_payment_statuses') }}</option>
                            @foreach ($paymentStatuses as $paymentStatus)
                                <option
                                    value="{{ $paymentStatus->value }}"
                                    @selected(($filters['payment_status'] ?? '') === $paymentStatus->value)
                                >
                                    {{ __('app.payment_status.' . $paymentStatus->value) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="date_from" class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('app.admin.appointments.filters.date_from') }}
                        </label>
                        <input
                            id="date_from"
                            name="date_from"
                            type="date"
                            value="{{ $filters['date_from'] ?? '' }}"
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-umbralia-accent focus:ring-umbralia-accent/30"
                            @change="submitFilters()"
                        >
                    </div>

                    <div>
                        <label for="date_to" class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('app.admin.appointments.filters.date_to') }}
                        </label>
                        <input
                            id="date_to"
                            name="date_to"
                            type="date"
                            value="{{ $filters['date_to'] ?? '' }}"
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-umbralia-accent focus:ring-umbralia-accent/30"
                            @change="submitFilters()"
                        >
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button
                        type="submit"
                        class="rounded-xl bg-umbralia-title px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-umbralia-title/90"
                    >
                        {{ __('app.admin.appointments.filters.apply') }}
                    </button>

                    <button
                        type="button"
                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        @click="resetFilters()"
                    >
                        {{ __('app.admin.appointments.filters.reset') }}
                    </button>

                    <p class="text-xs text-slate-500">
                        {{ __('app.admin.appointments.auto_refresh') }}
                    </p>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-semibold text-umbralia-title">
                    {{ __('app.admin.appointments.list_title') }}
                </h2>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse ($appointments as $appointment)
                    <article class="grid gap-4 px-5 py-5 lg:grid-cols-[1.3fr_1fr_1fr_auto] lg:items-center">
                        <div>
                            <p class="text-sm font-semibold text-slate-950">
                                {{ $appointment->patient_name }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $appointment->patient_email }}
                            </p>
                            <p class="mt-2 text-xs text-slate-500">
                                {{ __('app.admin.appointments.patient_timezone') }}:
                                {{ $appointment->patient_timezone }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-slate-950">
                                {{ $appointment->therapist->user->name }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $appointment->sessionType->name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-slate-950">
                                {{ $timezoneService->formatForDisplay($appointment->starts_at, 'd/m/Y H:i') }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ __('app.admin.appointments.ends_at') }}:
                                {{ $timezoneService->formatForDisplay($appointment->ends_at, 'H:i') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2 lg:justify-end">
                            <x-appointment-status-badge :status="$appointment->status" />
                            <x-payment-status-badge :status="$appointment->payment_status" />
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm font-medium text-slate-700">
                            {{ __('app.admin.appointments.empty') }}
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($appointments->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $appointments->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection

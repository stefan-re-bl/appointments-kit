@extends('layouts.admin', [
    'title' => __('app.admin.appointments.title'),
    'header' => __('app.admin.appointments.title'),
])

@section('content')
    <div
        x-data="adminAppointmentsCalendar(@js([
            'buttonText' => [
                'day' => __('app.admin.appointments.calendar.day'),
                'month' => __('app.admin.appointments.calendar.month'),
                'today' => __('app.admin.appointments.calendar.today'),
                'week' => __('app.admin.appointments.calendar.week'),
            ],
            'dayUrl' => route('admin.appointments.day'),
            'eventsUrl' => route('admin.appointments.events'),
            'labels' => [
                'close' => __('app.admin.appointments.day_modal.close'),
                'dayTitle' => __('app.admin.appointments.day_modal.title'),
                'empty' => __('app.admin.appointments.day_modal.empty'),
                'loadError' => __('app.admin.appointments.calendar.load_error'),
                'loading' => __('app.admin.appointments.day_modal.loading'),
                'patientTimezone' => __('app.admin.appointments.patient_timezone'),
                'price' => __('app.admin.appointments.day_modal.price'),
            ],
            'locale' => app()->getLocale(),
            'timezone' => $timezone,
        ]))"
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
            <div>
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="professional_id" class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('app.admin.appointments.filters.professional') }}
                        </label>
                        <select
                            id="professional_id"
                            name="professional_id"
                            x-ref="professionalFilter"
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                            @change="filterChanged()"
                        >
                            <option value="">{{ __('app.admin.appointments.filters.all_professionals') }}</option>
                            @foreach ($professionals as $professional)
                                <option
                                    value="{{ $professional->id }}"
                                    @selected((string) ($filters['professional_id'] ?? '') === (string) $professional->id)
                                >
                                    {{ $professional->user->name }}
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
                            x-ref="statusFilter"
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                            @change="filterChanged()"
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
                            x-ref="paymentFilter"
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                            @change="filterChanged()"
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
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        @click="resetFilters()"
                    >
                        {{ __('app.admin.appointments.filters.reset') }}
                    </button>

                    <p class="text-xs text-slate-500">
                        {{ __('app.admin.appointments.calendar.filter_help') }}
                    </p>

                    <p class="text-xs text-slate-500">
                        {{ __('app.admin.appointments.auto_refresh') }}
                    </p>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-semibold text-brand-title">
                    {{ __('app.admin.appointments.calendar.title') }}
                </h2>
                <p class="mt-1 text-sm text-slate-600">
                    {{ __('app.admin.appointments.calendar.help') }}
                </p>
            </div>

            <div class="admin-appointments-calendar p-4 sm:p-5">
                <div x-ref="calendar"></div>
            </div>
        </section>

        <div
            x-cloak
            x-show="dayModalOpen"
            x-transition.opacity
            class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/70 px-4 py-8"
            role="dialog"
            aria-modal="true"
            aria-labelledby="appointments-day-title"
            @keydown.escape.window="closeDay()"
        >
            <div class="absolute inset-0" @click="closeDay()"></div>

            <section class="relative flex max-h-[calc(100svh-4rem)] w-full max-w-4xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-900/10">
                <header class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 id="appointments-day-title" class="text-base font-semibold text-brand-title" x-text="dayTitle"></h2>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ __('app.admin.appointments.day_modal.subtitle') }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-accent/30"
                        @click="closeDay()"
                        title="{{ __('app.admin.appointments.day_modal.close') }}"
                        aria-label="{{ __('app.admin.appointments.day_modal.close') }}"
                    >
                        <span aria-hidden="true">×</span>
                    </button>
                </header>

                <div class="overflow-y-auto px-5 py-5">
                    <p x-show="dayLoading" class="text-sm text-slate-600">
                        {{ __('app.admin.appointments.day_modal.loading') }}
                    </p>

                    <p x-show="dayError" x-text="dayError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></p>

                    <template x-if="! dayLoading && ! dayError && dayAppointments.length === 0">
                        <p class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-600">
                            {{ __('app.admin.appointments.day_modal.empty') }}
                        </p>
                    </template>

                    <div x-show="! dayLoading && ! dayError && dayAppointments.length > 0" class="space-y-3">
                        <template x-for="appointment in dayAppointments" :key="appointment.id">
                            <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="grid gap-4 lg:grid-cols-[9rem_minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-start">
                                    <p class="text-sm font-semibold text-slate-950" x-text="appointment.time_range"></p>

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-950" x-text="appointment.customer_name"></p>
                                        <p class="mt-1 truncate text-sm text-slate-500" x-text="appointment.customer_email"></p>
                                        <p class="mt-2 text-xs text-slate-500">
                                            {{ __('app.admin.appointments.patient_timezone') }}:
                                            <span x-text="appointment.customer_timezone"></span>
                                        </p>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-slate-950" x-text="appointment.professional_name"></p>
                                        <p class="mt-1 truncate text-sm text-slate-500" x-text="appointment.session_type"></p>
                                        <p class="mt-2 text-xs font-semibold text-slate-700" x-text="appointment.price"></p>
                                    </div>

                                    <div class="flex flex-wrap gap-2 lg:justify-end">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                            :class="statusBadgeClass(appointment.status)"
                                            x-text="appointment.status_label"
                                        ></span>
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1"
                                            :class="paymentBadgeClass(appointment.payment_status)"
                                            x-text="appointment.payment_status_label"
                                        ></span>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

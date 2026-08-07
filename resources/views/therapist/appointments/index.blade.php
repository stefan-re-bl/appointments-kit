<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-slate-900">
                {{ __('app.appointments.management.title') }}
            </h2>

            <p class="mt-1 text-sm text-slate-600">
                {{ __('app.appointments.management.subtitle', ['timezone' => $therapistTimezone]) }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto w-full max-w-5xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <div
                x-data="adminAppointmentsCalendar(@js([
                    'buttonText' => [
                        'day' => __('app.appointments.management.calendar.day'),
                        'month' => __('app.appointments.management.calendar.month'),
                        'today' => __('app.appointments.management.calendar.today'),
                        'week' => __('app.appointments.management.calendar.week'),
                    ],
                    'dayUrl' => route('therapist.appointments.day'),
                    'eventsUrl' => route('therapist.appointments.events'),
                    'labels' => [
                        'close' => __('app.appointments.management.day_modal.close'),
                        'dayTitle' => __('app.appointments.management.day_modal.title'),
                        'empty' => __('app.appointments.management.day_modal.empty'),
                        'loadError' => __('app.appointments.management.calendar.load_error'),
                        'loading' => __('app.appointments.management.day_modal.loading'),
                        'patientTimezone' => __('app.appointments.management.patient_timezone'),
                    ],
                    'locale' => app()->getLocale(),
                    'timezone' => $therapistTimezone,
                ]))"
                class="space-y-6"
            >
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="status" class="mb-1 block text-sm font-medium text-slate-700">
                                {{ __('app.appointments.management.filters.status') }}
                            </label>
                            <select
                                id="status"
                                name="status"
                                x-ref="statusFilter"
                                class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-umbralia-accent focus:ring-umbralia-accent/30"
                                @change="filterChanged()"
                            >
                                <option value="">{{ __('app.appointments.management.filters.all_statuses') }}</option>
                                @foreach ($appointmentStatuses as $status)
                                    <option value="{{ $status->value }}">
                                        {{ __('app.appointment_status.' . $status->value) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="payment_status" class="mb-1 block text-sm font-medium text-slate-700">
                                {{ __('app.appointments.management.filters.payment_status') }}
                            </label>
                            <select
                                id="payment_status"
                                name="payment_status"
                                x-ref="paymentFilter"
                                class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-umbralia-accent focus:ring-umbralia-accent/30"
                                @change="filterChanged()"
                            >
                                <option value="">{{ __('app.appointments.management.filters.all_payment_statuses') }}</option>
                                @foreach ($paymentStatuses as $paymentStatus)
                                    <option value="{{ $paymentStatus->value }}">
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
                            {{ __('app.appointments.management.filters.reset') }}
                        </button>

                        <p class="text-xs text-slate-500">
                            {{ __('app.appointments.management.calendar.filter_help') }}
                        </p>

                        <p class="text-xs text-slate-500">
                            {{ __('app.appointments.management.auto_refresh') }}
                        </p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h3 class="text-base font-semibold text-umbralia-title">
                            {{ __('app.appointments.management.calendar.title') }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ __('app.appointments.management.calendar.help') }}
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
                    aria-labelledby="therapist-appointments-day-title"
                    @keydown.escape.window="closeDay()"
                >
                    <div class="absolute inset-0" @click="closeDay()"></div>

                    <section class="relative flex max-h-[calc(100svh-4rem)] w-full max-w-4xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-900/10">
                        <header class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
                            <div>
                                <h3 id="therapist-appointments-day-title" class="text-base font-semibold text-umbralia-title" x-text="dayTitle"></h3>
                                <p class="mt-1 text-sm text-slate-600">
                                    {{ __('app.appointments.management.day_modal.subtitle') }}
                                </p>
                            </div>

                            <button
                                type="button"
                                class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-umbralia-accent/30"
                                @click="closeDay()"
                                title="{{ __('app.appointments.management.day_modal.close') }}"
                                aria-label="{{ __('app.appointments.management.day_modal.close') }}"
                            >
                                <span aria-hidden="true">×</span>
                            </button>
                        </header>

                        <div class="overflow-y-auto px-5 py-5">
                            <p x-show="dayLoading" class="text-sm text-slate-600">
                                {{ __('app.appointments.management.day_modal.loading') }}
                            </p>

                            <p x-show="dayError" x-text="dayError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></p>

                            <template x-if="! dayLoading && ! dayError && dayAppointments.length === 0">
                                <p class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-600">
                                    {{ __('app.appointments.management.day_modal.empty') }}
                                </p>
                            </template>

                            <div x-show="! dayLoading && ! dayError && dayAppointments.length > 0" class="space-y-3">
                                <template x-for="appointment in dayAppointments" :key="appointment.id">
                                    <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="grid gap-4 lg:grid-cols-[9rem_minmax(0,1fr)_auto] lg:items-start">
                                            <p class="text-sm font-semibold text-slate-950" x-text="appointment.time_range"></p>

                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-slate-950" x-text="appointment.patient_name"></p>
                                                <p class="mt-1 truncate text-sm text-slate-500" x-text="appointment.patient_email"></p>
                                                <p class="mt-2 text-xs text-slate-500">
                                                    {{ __('app.appointments.management.patient_timezone') }}:
                                                    <span x-text="appointment.patient_timezone"></span>
                                                </p>
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

            <section class="mt-8">
                <h3 class="mb-4 text-base font-semibold text-slate-900">
                    {{ __('app.appointments.management.list_title') }}
                </h3>

                <div class="space-y-4">
                @forelse ($appointments as $appointment)
                    <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-base font-semibold text-slate-900">
                                            {{ $appointment->patient_name }}
                                        </h3>

                                        <p class="truncate text-sm text-slate-500">
                                            {{ $appointment->patient_email }}
                                        </p>
                                    </div>

                                    <div class="mt-2 sm:mt-0">
                                        <x-payment-status-badge :status="$appointment->payment_status" />
                                    </div>
                                </div>

                                <dl class="mt-5 grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                            {{ __('app.appointments.management.schedule') }}
                                        </dt>

                                        <dd class="mt-1 text-sm text-slate-900">
                                            {{ $timezoneService->formatForDisplay($appointment->starts_at, 'd/m/Y H:i') }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                            {{ __('app.payments.label') }}
                                        </dt>

                                        <dd class="mt-1 text-sm text-slate-900">
                                            {{ __('app.payments.status.' . $appointment->payment_status->value) }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                            {{ __('app.payments.paid_at_label') }}
                                        </dt>

                                        <dd class="mt-1 text-sm text-slate-900">
                                            @if ($appointment->paid_at !== null)
                                                {{ $timezoneService->formatForDisplay($appointment->paid_at, 'd/m/Y H:i') }}
                                            @else
                                                {{ __('app.payments.not_registered') }}
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <form
                                method="POST"
                                action="{{ route('therapist.appointments.payment.update', $appointment) }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 p-4 lg:w-64"
                            >
                                @csrf
                                @method('PATCH')

                                <label
                                    for="payment_status_{{ $appointment->id }}"
                                    class="block text-sm font-medium text-slate-700"
                                >
                                    {{ __('app.payments.label') }}
                                </label>

                                <select
                                    id="payment_status_{{ $appointment->id }}"
                                    name="payment_status"
                                    class="mt-2 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-umbralia-accent focus:ring-umbralia-accent/30"
                                >
                                    @foreach ($paymentStatuses as $paymentStatus)
                                        <option
                                            value="{{ $paymentStatus->value }}"
                                            @selected($appointment->payment_status === $paymentStatus)
                                        >
                                            {{ __('app.payments.status.' . $paymentStatus->value) }}
                                        </option>
                                    @endforeach
                                </select>

                                <button
                                    type="submit"
                                    class="mt-3 inline-flex w-full items-center justify-center rounded-lg bg-umbralia-title px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-umbralia-title/90 focus:outline-none focus:ring-2 focus:ring-umbralia-accent/30 focus:ring-offset-2"
                                >
                                    {{ __('app.payments.update') }}
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl bg-white p-10 text-center shadow-sm ring-1 ring-slate-200">
                        <p class="text-sm text-slate-500">
                            {{ __('app.appointments.management.empty') }}
                        </p>
                    </div>
                @endforelse
                </div>

                @if ($appointments->hasPages())
                    <div class="mt-6">
                        {{ $appointments->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>

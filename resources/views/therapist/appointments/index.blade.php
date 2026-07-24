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

                                <dl class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                    <div>
                                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                            {{ __('app.appointments.management.session') }}
                                        </dt>

                                        <dd class="mt-1 text-sm text-slate-900">
                                            {{ $appointment->sessionType?->name ?? __('app.appointment_public.unavailable') }}
                                        </dd>

                                        <dd class="mt-1 text-sm text-slate-500">
                                            {{ $appointment->price }} {{ $appointment->currency }}
                                        </dd>
                                    </div>

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
        </div>
    </div>
</x-app-layout>

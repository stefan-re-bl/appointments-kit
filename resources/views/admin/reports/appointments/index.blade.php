@extends('layouts.admin')

@section('content')
    @php
        $exportFilters = array_filter($filters, static fn ($value) => $value !== null && $value !== '');
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">
                    {{ __('reports.appointments.eyebrow') }}
                </p>

                <h1 class="mt-1 text-2xl font-semibold text-slate-900">
                    {{ __('reports.appointments.title') }}
                </h1>

                <p class="mt-2 max-w-3xl text-sm text-slate-600">
                    {{ __('reports.appointments.description') }}
                </p>

                <p class="mt-2 text-xs text-slate-500">
                    {{ __('reports.appointments.timezone_notice', ['timezone' => $report['timezone']]) }}
                </p>
            </div>

            <a
                href="{{ route('admin.reports.appointments.export', $exportFilters) }}"
                class="inline-flex items-center justify-center rounded-xl bg-brand-title px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-title/90"
            >
                {{ __('reports.actions.export_csv') }}
            </a>
        </div>

        <form
            method="GET"
            action="{{ route('admin.reports.appointments.index') }}"
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div class="grid gap-4 md:grid-cols-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-slate-700">
                        {{ __('reports.filters.date_from') }}
                    </label>

                    <input
                        id="date_from"
                        name="date_from"
                        type="date"
                        value="{{ $filters['date_from'] }}"
                        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                    >

                    @error('date_from')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-slate-700">
                        {{ __('reports.filters.date_to') }}
                    </label>

                    <input
                        id="date_to"
                        name="date_to"
                        type="date"
                        value="{{ $filters['date_to'] }}"
                        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                    >

                    @error('date_to')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="professional_id" class="block text-sm font-medium text-slate-700">
                        {{ __('reports.filters.professional') }}
                    </label>

                    <select
                        id="professional_id"
                        name="professional_id"
                        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                    >
                        <option value="">{{ __('reports.filters.all_professionals') }}</option>

                        @foreach ($professionals as $professional)
                            <option
                                value="{{ $professional->id }}"
                                @selected((string) $filters['professional_id'] === (string) $professional->id)
                            >
                                {{ $professional->user?->name ?? __('reports.values.no_professional_name') }}
                            </option>
                        @endforeach
                    </select>

                    @error('professional_id')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-brand-title px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-title/90"
                    >
                        {{ __('reports.actions.apply_filters') }}
                    </button>

                    <a
                        href="{{ route('admin.reports.appointments.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        {{ __('reports.actions.clear') }}
                    </a>
                </div>
            </div>
        </form>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('reports.metrics.total_appointments') }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $report['metrics']['total_appointments'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('reports.metrics.cancelled_appointments') }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $report['metrics']['cancelled_appointments'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('reports.metrics.paid_appointments') }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $report['metrics']['paid_appointments'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('reports.metrics.pending_payment_appointments') }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $report['metrics']['pending_payment_appointments'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('reports.metrics.payment_conversion_rate') }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">
                    {{ number_format((float) $report['metrics']['payment_conversion_rate'], 2, ',', '.') }}%
                </p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">
                    {{ __('reports.sections.amounts_by_currency') }}
                </h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-3">{{ __('reports.table.currency') }}</th>
                                <th class="px-3 py-3">{{ __('reports.table.estimated_amount') }}</th>
                                <th class="px-3 py-3">{{ __('reports.table.collected_amount') }}</th>
                                <th class="px-3 py-3">{{ __('reports.table.pending_amount') }}</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse ($report['currency_summaries'] as $summary)
                                <tr>
                                    <td class="px-3 py-3 font-medium text-slate-900">{{ $summary['currency'] }}</td>
                                    <td class="px-3 py-3 text-slate-700">
                                        {{ number_format((float) $summary['estimated_amount'], 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 text-slate-700">
                                        {{ number_format((float) $summary['collected_amount'], 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 text-slate-700">
                                        {{ number_format((float) $summary['pending_amount'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-slate-500">
                                        {{ __('reports.empty.no_amounts') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">
                    {{ __('reports.sections.pending_by_professional') }}
                </h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-3">{{ __('reports.table.professional') }}</th>
                                <th class="px-3 py-3">{{ __('reports.table.currency') }}</th>
                                <th class="px-3 py-3">{{ __('reports.table.pending_appointments') }}</th>
                                <th class="px-3 py-3">{{ __('reports.table.pending_amount') }}</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse ($report['professional_summaries'] as $summary)
                                <tr>
                                    <td class="px-3 py-3">
                                        <div class="font-medium text-slate-900">{{ $summary['professional_name'] }}</div>
                                        <div class="text-xs text-slate-500">{{ $summary['professional_email'] }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-slate-700">{{ $summary['currency'] }}</td>
                                    <td class="px-3 py-3 text-slate-700">{{ $summary['pending_payment_appointments'] }}</td>
                                    <td class="px-3 py-3 text-slate-700">
                                        {{ number_format((float) $summary['pending_amount'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-slate-500">
                                        {{ __('reports.empty.no_pending_payments') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">
                        {{ __('reports.sections.appointment_audit') }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('reports.sections.appointment_audit_description') }}
                    </p>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-3">{{ __('reports.table.patient') }}</th>
                            <th class="px-3 py-3">{{ __('reports.table.professional') }}</th>
                            <th class="px-3 py-3">{{ __('reports.table.session_type') }}</th>
                            <th class="px-3 py-3">{{ __('reports.table.starts_at') }}</th>
                            <th class="px-3 py-3">{{ __('reports.table.appointment_status') }}</th>
                            <th class="px-3 py-3">{{ __('reports.table.payment_status') }}</th>
                            <th class="px-3 py-3">{{ __('reports.table.paid_at') }}</th>
                            <th class="px-3 py-3">{{ __('reports.table.amount') }}</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($report['rows'] as $row)
                            <tr>
                                <td class="px-3 py-3">
                                    <div class="font-medium text-slate-900">{{ $row['customer_name'] }}</div>
                                    <div class="text-xs text-slate-500">{{ $row['customer_email'] }}</div>
                                </td>

                                <td class="px-3 py-3">
                                    <div class="font-medium text-slate-900">{{ $row['professional_name'] }}</div>
                                    <div class="text-xs text-slate-500">{{ $row['professional_email'] }}</div>
                                </td>

                                <td class="px-3 py-3 text-slate-700">{{ $row['session_type_name'] }}</td>

                                <td class="px-3 py-3 text-slate-700">
                                    {{ $row['starts_at_display'] }}
                                </td>

                                <td class="px-3 py-3">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                        {{ __('reports.status.appointment.' . $row['status']) }}
                                    </span>
                                </td>

                                <td class="px-3 py-3">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                        {{ __('reports.status.payment.' . $row['payment_status']) }}
                                    </span>
                                </td>

                                <td class="px-3 py-3 text-slate-700">
                                    {{ $row['paid_at_display'] ?? __('reports.values.not_paid') }}
                                </td>

                                <td class="px-3 py-3 text-slate-700">
                                    {{ number_format((float) $row['price'], 2, ',', '.') }} {{ $row['currency'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-8 text-center text-slate-500">
                                    {{ __('reports.empty.no_appointments') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

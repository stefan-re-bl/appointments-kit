<?php /** @var \App\Models\Appointment $appointment */ ?>

@extends('layouts.guest')

@section('title', __('app.appointment_public.title'))

@section('content')
    <div class="min-h-screen bg-[#fbf9fc] py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold tracking-tight text-slate-950">
                    {{ __('app.appointment_public.title') }}
                </h1>

                <p class="mt-2 text-sm text-slate-600">
                    {{ __('app.appointment_public.subtitle') }}
                </p>
            </div>

            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                    {{ session('warning') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-indigo-800 bg-indigo-950 px-6 py-5">
                    <p class="text-sm font-medium text-slate-200">
                        {{ __('app.appointment_public.appointment_code') }}
                    </p>

                    <p class="mt-1 break-all font-mono text-sm text-white">
                        {{ $appointment->token }}
                    </p>
                </div>

                <div class="grid gap-6 p-6">
                    <section>
                        <h2 class="text-lg font-semibold text-slate-950">
                            {{ __('app.appointment_public.sections.patient') }}
                        </h2>

                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                    <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.patient_name') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-950">
                                    {{ $appointment->patient_name }}
                                </dd>
                            </div>

                            <div>
                                    <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.patient_email') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-950">
                                    {{ $appointment->patient_email }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="border-t border-slate-200 pt-6">
                        <h2 class="text-lg font-semibold text-slate-950">
                            {{ __('app.appointment_public.sections.appointment') }}
                        </h2>

                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                    <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.therapist') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-950">
                                    {{ $appointment->therapist?->user?->name ?? __('app.appointment_public.unavailable') }}
                                </dd>
                            </div>

                            <div>
                                    <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.session_type') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-950">
                                    {{ $appointment->sessionType?->name ?? __('app.appointment_public.unavailable') }}
                                </dd>
                            </div>

                            <div>
                                    <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.starts_at') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-950">
                                    {{ $startsAt }}
                                </dd>
                            </div>

                            <div>
                                    <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.ends_at') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-950">
                                    {{ $endsAt }}
                                </dd>
                            </div>

                            <div>
                                    <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.timezone') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-950">
                                    {{ $patientTimezone }}
                                </dd>
                            </div>

                            <div>
                                    <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.status') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-950">
                                    {{ $appointmentStatusLabel }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="border-t border-slate-200 pt-6">
                        <h2 class="text-lg font-semibold text-slate-950">
                            {{ __('app.appointment_public.sections.payment') }}
                        </h2>

                        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div>
                                <x-payment-status-badge :status="$appointment->payment_status" />
                            </div>

                            @if ($paidAt)
                                <p class="mt-3 text-sm text-slate-600">
                                    {{ __('app.appointment_public.fields.paid_at') }}: {{ $paidAt }}
                                </p>
                            @else
                                <p class="mt-3 text-sm text-slate-600">
                                    {{ __('app.appointment_public.payment_manual_note') }}
                                </p>
                            @endif

                            @if (filled($appointment->therapist?->payment_instructions))
                                <div class="mt-4 border-t border-slate-200 pt-4">
                                    <h3 class="text-sm font-semibold text-slate-900">
                                        {{ __('app.appointment_public.payment_instructions_title') }}
                                    </h3>
                                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                                        {{ $appointment->therapist->payment_instructions }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="border-t border-slate-200 pt-6">
                        <h2 class="text-lg font-semibold text-slate-950">
                            {{ __('appointment_policy.sections.policy') }}
                        </h2>

                        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-800">
                                {{ $policyMessage }}
                            </p>
                        </div>
                    </section>

                    <section class="border-t border-slate-200 pt-6">
                        <h2 class="text-lg font-semibold text-slate-950">
                            {{ __('app.appointment_public.sections.actions') }}
                        </h2>

                        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                            @if ($canJoinMeet)
                                <a
                                    href="{{ $appointment->therapist->google_meet_link }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center rounded-lg bg-indigo-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800"
                                >
                                    {{ __('app.appointment_public.actions.join_meet') }}
                                </a>
                            @endif

                            @if ($canCancel)
                                <form method="POST" action="{{ route('appointments.public.cancel', $appointment->token) }}">
                                    @csrf

                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-lg bg-rose-800 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 sm:w-auto"
                                    >
                                        {{ $canRefund
                                            ? __('appointment_policy.actions.cancel_with_refund')
                                            : __('appointment_policy.actions.cancel_without_refund')
                                        }}
                                    </button>
                                </form>
                            @endif

                            @if ($canReschedule && filled($rescheduleUrl ?? null))
                                <a
                                    href="{{ $rescheduleUrl }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800"
                                >
                                    {{ __('appointment_policy.actions.request_reschedule') }}
                                </a>
                            @endif

                            @if ($canContactTherapist)
                                <a
                                    href="mailto:{{ $appointment->therapist->user->email }}?subject={{ rawurlencode(__('app.appointment_public.actions.contact_subject')) }}"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50"
                                >
                                    {{ __('app.appointment_public.actions.contact_therapist') }}
                                </a>
                            @endif

                            <a
                                href="{{ route('contact.create', ['type' => 'booking_problem']) }}"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50"
                            >
                                {{ __('app.appointment_public.actions.contact_support') }}
                            </a>

                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection

<?php /** @var \App\Models\Appointment $appointment */ ?>

@extends('layouts.guest')

@section('title', __('app.appointment_public.title'))

@section('content')
    <div class="min-h-screen bg-slate-50 py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ __('app.appointment_public.title') }}
                </h1>

                <p class="mt-2 text-sm text-slate-600">
                    {{ __('app.appointment_public.subtitle') }}
                </p>
            </div>

            @if (session('success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm font-medium text-yellow-800">
                    {{ session('warning') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-200 bg-slate-900 px-6 py-5">
                    <p class="text-sm font-medium text-slate-300">
                        {{ __('app.appointment_public.appointment_code') }}
                    </p>

                    <p class="mt-1 break-all font-mono text-sm text-white">
                        {{ $appointment->token }}
                    </p>
                </div>

                <div class="grid gap-6 p-6">
                    <section>
                        <h2 class="text-lg font-semibold text-slate-900">
                            {{ __('app.appointment_public.sections.patient') }}
                        </h2>

                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.patient_name') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-900">
                                    {{ $appointment->patient_name }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.patient_email') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-900">
                                    {{ $appointment->patient_email }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="border-t border-slate-200 pt-6">
                        <h2 class="text-lg font-semibold text-slate-900">
                            {{ __('app.appointment_public.sections.appointment') }}
                        </h2>

                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.therapist') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-900">
                                    {{ $appointment->therapist?->user?->name ?? __('app.appointment_public.unavailable') }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.session_type') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-900">
                                    {{ $appointment->sessionType?->name ?? __('app.appointment_public.unavailable') }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.starts_at') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-900">
                                    {{ $startsAt }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.ends_at') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-900">
                                    {{ $endsAt }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.timezone') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-900">
                                    {{ $patientTimezone }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-slate-500">
                                    {{ __('app.appointment_public.fields.status') }}
                                </dt>
                                <dd class="mt-1 text-sm text-slate-900">
                                    {{ $appointmentStatusLabel }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="border-t border-slate-200 pt-6">
                        <h2 class="text-lg font-semibold text-slate-900">
                            {{ __('app.appointment_public.sections.payment') }}
                        </h2>

                        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-medium text-slate-900">
                                {{ $paymentStatusLabel }}
                            </p>

                            @if ($paidAt)
                                <p class="mt-1 text-sm text-slate-600">
                                    {{ __('app.appointment_public.fields.paid_at') }}: {{ $paidAt }}
                                </p>
                            @else
                                <p class="mt-1 text-sm text-slate-600">
                                    {{ __('app.appointment_public.payment_manual_note') }}
                                </p>
                            @endif
                        </div>
                    </section>

                    <section class="border-t border-slate-200 pt-6">
                        <h2 class="text-lg font-semibold text-slate-900">
                            {{ __('appointment_policy.sections.policy') }}
                        </h2>

                        <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4">
                            <p class="text-sm text-blue-900">
                                {{ $policyMessage }}
                            </p>
                        </div>
                    </section>

                    <section class="border-t border-slate-200 pt-6">
                        <h2 class="text-lg font-semibold text-slate-900">
                            {{ __('app.appointment_public.sections.actions') }}
                        </h2>

                        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                            @if ($canJoinMeet)
                                <a
                                    href="{{ $appointment->therapist->google_meet_link }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700"
                                >
                                    {{ __('app.appointment_public.actions.join_meet') }}
                                </a>
                            @endif

                            @if ($canCancel)
                                <form method="POST" action="{{ route('appointments.public.cancel', $appointment->token) }}">
                                    @csrf

                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500 sm:w-auto"
                                    >
                                        {{ $canRefund
                                            ? __('appointment_policy.actions.cancel_with_refund')
                                            : __('appointment_policy.actions.cancel_without_refund')
                                        }}
                                    </button>
                                </form>
                            @endif

                            @if ($canReschedule && filled($rescheduleMailto))
                                <a
                                    href="{{ $rescheduleMailto }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                                >
                                    {{ __('appointment_policy.actions.request_reschedule') }}
                                </a>
                            @endif

                            @if ($canContactTherapist)
                                <a
                                    href="mailto:{{ $appointment->therapist->user->email }}?subject={{ rawurlencode(__('app.appointment_public.actions.contact_subject')) }}"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-slate-50"
                                >
                                    {{ __('app.appointment_public.actions.contact_therapist') }}
                                </a>
                            @endif

                            @if (! $canJoinMeet && ! $canCancel && ! $canReschedule && ! $canContactTherapist)
                                <p class="text-sm text-slate-600">
                                    {{ __('app.appointment_public.actions.no_actions_available') }}
                                </p>
                            @endif
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection
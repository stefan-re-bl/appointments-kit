@extends('layouts.guest')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('book.time') }}" class="mb-6 inline-flex items-center text-sm font-semibold text-indigo-700 hover:text-indigo-800">&larr; {{ __('app.change_date') }}</a>

    @include('book.partials.stepper', ['currentStep' => 5])

    <h1 class="mb-8 text-center text-3xl font-bold tracking-tight text-slate-950">{{ __('app.confirm_booking') }}</h1>

    <!-- Mensaje de Error General (del try/catch del controlador) -->
    @error('general')
        <div class="relative mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800" role="alert">
            <strong class="font-bold">{{ __('app.error') }}!</strong>
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    @enderror

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50 p-6">
            <h2 class="text-lg font-semibold text-slate-950">{{ $sessionType->name }}</h2>
            <p class="text-slate-600">{{ $therapist->user->name }}</p>
        </div>

        <div class="space-y-4 p-6">
            <div class="flex justify-between gap-6">
                <span class="text-slate-500">{{ __('app.date') }}:</span>
                <span class="text-right font-medium text-slate-950">{{ \Carbon\Carbon::parse($dateLocal)->locale(app()->getLocale())->isoFormat('LL') }}</span>
            </div>

            <div class="flex justify-between gap-6">
                <span class="text-slate-500">{{ __('app.time') }}:</span>
                <span class="text-right font-medium text-slate-950">{{ $localTime }}</span>
            </div>

            <div class="flex justify-between gap-6">
                <span class="text-slate-500">{{ __('booking_timezone.country_label') }}:</span>
                <span class="text-right font-medium text-slate-950">
                    @if ($patientCountry && $patientTimezoneLabel)
                        {{ __('booking_timezone.location_summary', [
                            'country' => __('booking_timezone.countries.'.$patientCountry),
                            'region' => $patientTimezoneLabel,
                        ]) }}
                    @else
                        {{ $patientCountry ? __('booking_timezone.countries.'.$patientCountry) : $patientTimezone }}
                    @endif
                </span>
            </div>

            <div class="flex justify-between gap-6">
                <span class="text-slate-500">{{ __('app.duration') }}:</span>
                <span class="text-right font-medium text-slate-950">{{ $sessionType->duration_minutes }} min</span>
            </div>

            <div class="flex justify-between gap-6 border-t border-slate-200 pt-4">
                <span class="text-slate-500">{{ __('app.total') }}:</span>
                <span class="text-right text-lg font-bold text-indigo-700">{{ $sessionType->price }} {{ $sessionType->currency }}</span>
            </div>
        </div>
    </div>

    <form action="{{ route('book.store') }}" method="POST" class="mt-8">
        @csrf

        <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h3 class="mb-2 font-medium text-slate-950">{{ __('app.your_details') }}</h3>

            <div>
                <label for="patient_name" class="mb-1 block text-sm font-medium text-slate-800">{{ __('app.name') }}</label>
                <input
                    type="text"
                    name="patient_name"
                    id="patient_name"
                    value="{{ old('patient_name') }}"
                    required
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30 @error('patient_name') border-rose-400 @enderror"
                >

                @error('patient_name')
                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="patient_email" class="mb-1 block text-sm font-medium text-slate-800">{{ __('app.email') }}</label>
                <input
                    type="email"
                    name="patient_email"
                    id="patient_email"
                    value="{{ old('patient_email') }}"
                    required
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30 @error('patient_email') border-rose-400 @enderror"
                >

                @error('patient_email')
                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>

                <div class="ml-3">
                    <h3 class="text-sm font-medium text-amber-900">{{ __('app.payment_coordination_title') }}</h3>

                    <div class="mt-2 text-sm text-amber-800">
                        <p>{{ __('app.payment_coordination_text') }}</p>
                        <a href="{{ route('information.payment-and-cancellation') }}" class="mt-2 inline-flex font-semibold underline hover:text-amber-950" target="_blank" rel="noopener">
                            {{ __('information.booking_policy_link') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-4">
            <h3 class="text-sm font-semibold text-rose-950">{{ __('legal.booking.emergency_title') }}</h3>
            <p class="mt-2 text-sm leading-6 text-rose-800">{{ __('legal.booking.emergency_text') }}</p>
            <a href="{{ route('legal.emergency-notice') }}" class="mt-2 inline-flex text-sm font-semibold text-rose-900 underline hover:text-rose-700" target="_blank" rel="noopener">
                {{ __('legal.booking.emergency_link') }}
            </a>
        </div>

        <div class="mt-6 space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <label class="flex gap-3 text-sm text-slate-600">
                <input
                    type="checkbox"
                    name="accepted_terms"
                    value="1"
                    @checked(old('accepted_terms'))
                    class="mt-1 rounded border-indigo-300 text-slate-600 focus:ring-indigo-500/30"
                    required
                >
                <span>
                    {{ __('legal.booking.accept_terms_prefix') }}
                    <a href="{{ route('legal.terms') }}" class="font-semibold text-indigo-700 underline" target="_blank" rel="noopener">{{ __('legal.nav.terms') }}</a>
                    {{ __('legal.booking.accept_terms_and') }}
                    <a href="{{ route('legal.privacy') }}" class="font-semibold text-indigo-700 underline" target="_blank" rel="noopener">{{ __('legal.nav.privacy') }}</a>.
                </span>
            </label>
            @error('accepted_terms')
                <p class="text-sm text-rose-700">{{ $message }}</p>
            @enderror

            <label class="flex gap-3 text-sm text-slate-600">
                <input
                    type="checkbox"
                    name="accepted_email_communications"
                    value="1"
                    @checked(old('accepted_email_communications'))
                    class="mt-1 rounded border-indigo-300 text-slate-600 focus:ring-indigo-500/30"
                    required
                >
                <span>{{ __('legal.booking.accept_email_communications') }}</span>
            </label>
            @error('accepted_email_communications')
                <p class="text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="mt-6 flex w-full items-center justify-center rounded-lg bg-indigo-700 px-4 py-3 text-lg font-bold text-white shadow-sm transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2">
            {{ __('app.confirm_and_book') }}
        </button>
    </form>
</div>

@endsection

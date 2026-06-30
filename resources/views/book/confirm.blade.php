@extends('layouts.guest')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('book.time') }}" class="text-indigo-600 hover:text-indigo-800 text-sm mb-4 inline-block">&larr; {{ __('app.change_date') }}</a>

    <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">{{ __('app.confirm_booking') }}</h1>

    <!-- Mensaje de Error General (del try/catch del controlador) -->
    @error('general')
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">{{ __('app.error') }}!</strong>
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    @enderror

    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ $sessionType->name }}</h2>
            <p class="text-gray-600">{{ $therapist->user->name }}</p>
        </div>

        <div class="p-6 space-y-4">
            <div class="flex justify-between">
                <span class="text-gray-500">{{ __('app.date') }}:</span>
                <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($dateLocal)->locale(app()->getLocale())->isoFormat('LL') }}</span>
            </div>

            <div class="flex justify-between">
                <span class="text-gray-500">{{ __('app.time') }}:</span>
                <span class="font-medium text-gray-900">{{ $localTime }}</span>
            </div>

            <div class="flex justify-between gap-6">
                <span class="text-gray-500">{{ __('booking_timezone.country_label') }}:</span>
                <span class="font-medium text-gray-900 text-right">
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

            <div class="flex justify-between">
                <span class="text-gray-500">{{ __('app.duration') }}:</span>
                <span class="font-medium text-gray-900">{{ $sessionType->duration_minutes }} min</span>
            </div>

            <div class="flex justify-between">
                <span class="text-gray-500">{{ __('app.total') }}:</span>
                <span class="font-bold text-lg text-indigo-600">{{ $sessionType->price }} {{ $sessionType->currency }}</span>
            </div>
        </div>
    </div>

    <form action="{{ route('book.store') }}" method="POST" class="mt-8">
        @csrf

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 space-y-4">
            <h3 class="font-medium text-gray-900 mb-2">{{ __('app.your_details') }}</h3>

            <div>
                <label for="patient_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.name') }}</label>
                <input
                    type="text"
                    name="patient_name"
                    id="patient_name"
                    value="{{ old('patient_name') }}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('patient_name') border-red-500 @enderror"
                >

                @error('patient_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="patient_email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.email') }}</label>
                <input
                    type="email"
                    name="patient_email"
                    id="patient_email"
                    value="{{ old('patient_email') }}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 @error('patient_email') border-red-500 @enderror"
                >

                @error('patient_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>

                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">{{ __('app.payment_coordination_title') }}</h3>

                    <div class="mt-2 text-sm text-yellow-700">
                        <p>{{ __('app.payment_coordination_text') }}</p>
                        <a href="{{ route('information.payment-and-cancellation') }}" class="mt-2 inline-flex font-semibold underline hover:text-yellow-900" target="_blank" rel="noopener">
                            {{ __('information.booking_policy_link') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-4">
            <h3 class="text-sm font-semibold text-red-900">{{ __('legal.booking.emergency_title') }}</h3>
            <p class="mt-2 text-sm leading-6 text-red-800">{{ __('legal.booking.emergency_text') }}</p>
            <a href="{{ route('legal.emergency-notice') }}" class="mt-2 inline-flex text-sm font-semibold underline text-red-900 hover:text-red-700" target="_blank" rel="noopener">
                {{ __('legal.booking.emergency_link') }}
            </a>
        </div>

        <div class="mt-6 rounded-md border border-gray-200 bg-white p-4 space-y-4">
            <label class="flex gap-3 text-sm text-gray-700">
                <input
                    type="checkbox"
                    name="accepted_terms"
                    value="1"
                    @checked(old('accepted_terms'))
                    class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
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
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <label class="flex gap-3 text-sm text-gray-700">
                <input
                    type="checkbox"
                    name="accepted_email_communications"
                    value="1"
                    @checked(old('accepted_email_communications'))
                    class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    required
                >
                <span>{{ __('legal.booking.accept_email_communications') }}</span>
            </label>
            @error('accepted_email_communications')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full mt-6 bg-green-600 text-white py-3 px-4 rounded-md font-bold text-lg hover:bg-green-700 transition shadow-lg flex justify-center items-center">
            {{ __('app.confirm_and_book') }}
        </button>
    </form>
</div>

@endsection

@extends('layouts.guest')

@section('content')
<div class="max-w-md mx-auto">
    <a href="{{ route('book.session') }}" class="text-indigo-600 hover:text-indigo-800 text-sm mb-4 inline-block">&larr; {{ __('app.back') }}</a>
    
    <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">{{ __('app.select_date') }}</h1>

    <form
        action="{{ route('book.store.date') }}"
        method="POST"
        x-data="{
            detected: false,
            detectionFailed: false,
            timezoneConfirmed: @js($timezoneWasConfirmed),
            detectTimezone() {
                if (this.timezoneConfirmed) {
                    return;
                }

                try {
                    const detectedTimezone = window.Intl
                        ? Intl.DateTimeFormat().resolvedOptions().timeZone
                        : null;
                    const timezoneOptionExists = detectedTimezone
                        ? Array.from(this.$refs.timezone.options)
                            .some(option => option.value === detectedTimezone)
                        : false;

                    if (! timezoneOptionExists) {
                        this.detectionFailed = true;
                        return;
                    }

                    this.$refs.timezone.value = detectedTimezone;
                    this.detected = true;
                } catch (error) {
                    this.detectionFailed = true;
                }
            },
        }"
        x-init="detectTimezone()"
    >
        @csrf
        <div class="bg-white p-8 rounded-lg shadow-md border border-gray-200 space-y-6">
            <div>
                <label for="patient_timezone" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('booking_timezone.label') }}
                </label>
                <select
                    name="patient_timezone"
                    id="patient_timezone"
                    x-ref="timezone"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-gray-900 @error('patient_timezone') border-red-500 @enderror"
                >
                    @foreach ($timezones as $timezone)
                        <option value="{{ $timezone }}" @selected(old('patient_timezone', $patientTimezone) === $timezone)>
                            {{ $timezone }}
                        </option>
                    @endforeach
                </select>

                <p class="mt-2 text-sm text-gray-600">{{ __('booking_timezone.help') }}</p>
                <p x-show="detected" x-cloak class="mt-2 text-sm text-green-700">
                    {{ __('booking_timezone.detected') }}
                </p>
                <p x-show="detectionFailed" x-cloak class="mt-2 text-sm text-amber-700">
                    {{ __('booking_timezone.detection_failed') }}
                </p>

                @error('patient_timezone')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.date') }}</label>
                <input type="date" name="date" id="date" min="{{ $minDate }}" value="{{ old('date') }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-gray-900">
            </div>
        </div>

        <button type="submit" class="w-full mt-6 bg-indigo-600 text-white py-3 px-4 rounded-md font-semibold hover:bg-indigo-700 transition shadow-lg">
            {{ __('app.find_slots') }}
        </button>
    </form>
</div>
@endsection

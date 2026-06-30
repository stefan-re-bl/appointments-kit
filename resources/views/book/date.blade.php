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
            countryRegions: @js($countryRegions),
            regionLabels: @js($regionLabels),
            selectedCountry: @js(old('patient_country', $patientCountry)),
            selectedRegion: @js(old('patient_region', $patientRegion)),
            timezoneLocations: @js($timezoneLocations),
            timezoneConfirmed: @js($timezoneWasConfirmed),
            regionOptions() {
                return this.countryRegions[this.selectedCountry] ?? {};
            },
            hasRegions() {
                return Object.keys(this.regionOptions()).length > 0;
            },
            syncRegion() {
                if (! this.hasRegions()) {
                    this.selectedRegion = '';
                    return;
                }

                if (! this.regionOptions()[this.selectedRegion]) {
                    this.selectedRegion = '';
                }
            },
            detectTimezone() {
                if (this.timezoneConfirmed) {
                    return;
                }

                try {
                    const detectedTimezone = window.Intl
                        ? Intl.DateTimeFormat().resolvedOptions().timeZone
                        : null;
                    const detectedLocation = detectedTimezone
                        ? this.timezoneLocations[detectedTimezone]
                        : null;

                    if (! detectedLocation) {
                        this.detectionFailed = true;
                        return;
                    }

                    this.selectedCountry = detectedLocation.country;
                    this.selectedRegion = detectedLocation.region ?? '';
                    this.syncRegion();
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
                <label for="patient_country" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('booking_timezone.country_label') }}
                </label>
                <select
                    name="patient_country"
                    id="patient_country"
                    x-ref="country"
                    x-model="selectedCountry"
                    @change="syncRegion()"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-gray-900 @error('patient_country') border-red-500 @enderror"
                >
                    @foreach ($countries as $countryCode => $timezone)
                        <option value="{{ $countryCode }}" @selected(old('patient_country', $patientCountry) === $countryCode)>
                            {{ __('booking_timezone.countries.'.$countryCode) }}
                        </option>
                    @endforeach
                </select>

                <p class="mt-2 text-sm text-gray-600">{{ __('booking_timezone.country_help') }}</p>
                <p x-show="detected" x-cloak class="mt-2 text-sm text-green-700">
                    {{ __('booking_timezone.country_detected') }}
                </p>
                <p x-show="detectionFailed" x-cloak class="mt-2 text-sm text-amber-700">
                    {{ __('booking_timezone.country_detection_failed') }}
                </p>

                @error('patient_country')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="hasRegions()" x-cloak>
                <label for="patient_region" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('booking_timezone.region_label') }}
                </label>
                <select
                    name="patient_region"
                    id="patient_region"
                    x-model="selectedRegion"
                    :required="hasRegions()"
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-gray-900 @error('patient_region') border-red-500 @enderror"
                >
                    <option value="">{{ __('booking_timezone.region_placeholder') }}</option>
                    <template x-for="(timezone, regionCode) in regionOptions()" :key="regionCode">
                        <option :value="regionCode" x-text="regionLabels[selectedCountry][regionCode]"></option>
                    </template>
                </select>

                <p class="mt-2 text-sm text-gray-600">{{ __('booking_timezone.region_help') }}</p>

                @error('patient_region')
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

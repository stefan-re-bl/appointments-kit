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
            countryTimezones: @js($countryTimezones),
            selectedCountry: @js(old('patient_country', $patientCountry)),
            selectedDate: @js(old('date', $selectedDate)),
            selectedTimezone: @js(old('patient_timezone', $patientTimezone)),
            timezoneCountries: @js($timezoneCountries),
            timezoneConfirmed: @js($timezoneWasConfirmed),
            effectiveTimezoneOptions() {
                const groups = {};

                for (const timezone of this.countryTimezones[this.selectedCountry] ?? []) {
                    const offset = this.offsetForTimezone(timezone);
                    groups[offset] ??= [];
                    groups[offset].push(timezone);
                }

                return Object.entries(groups)
                    .sort(([offsetA], [offsetB]) => Number(offsetA) - Number(offsetB))
                    .map(([offset, timezones]) => {
                        timezones.sort();

                        return {
                            timezone: timezones.includes(this.selectedTimezone) ? this.selectedTimezone : timezones[0],
                            label: `${this.formatOffset(Number(offset))} (${this.timezoneSamples(timezones)})`,
                            timezones,
                        };
                    });
            },
            hasTimezoneRegions() {
                return this.effectiveTimezoneOptions().length > 1;
            },
            syncTimezone() {
                const options = this.effectiveTimezoneOptions();

                if (options.length <= 1) {
                    this.selectedTimezone = '';
                    return;
                }

                const currentOption = options.find(option => option.timezones.includes(this.selectedTimezone));

                if (currentOption) {
                    this.selectedTimezone = currentOption.timezone;
                    return;
                }

                this.selectedTimezone = options[0]?.timezone ?? '';
            },
            detectTimezone() {
                if (this.timezoneConfirmed) {
                    return;
                }

                try {
                    const detectedTimezone = window.Intl
                        ? Intl.DateTimeFormat().resolvedOptions().timeZone
                        : null;
                    const detectedCountry = detectedTimezone
                        ? this.timezoneCountries[detectedTimezone]?.country
                        : null;

                    if (! detectedCountry) {
                        this.detectionFailed = true;
                        return;
                    }

                    this.selectedCountry = detectedCountry;
                    this.selectedTimezone = detectedTimezone;
                    this.syncTimezone();
                    this.detected = true;
                } catch (error) {
                    this.detectionFailed = true;
                }
            },
            offsetForTimezone(timezone) {
                const date = this.selectedDate || @js($minDate);
                const timestamp = Date.parse(`${date}T12:00:00Z`);
                const parts = new Intl.DateTimeFormat('en-US', {
                    timeZone: timezone,
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hourCycle: 'h23',
                }).formatToParts(new Date(timestamp));
                const values = Object.fromEntries(parts.map(part => [part.type, part.value]));
                const localAsUtc = Date.UTC(
                    Number(values.year),
                    Number(values.month) - 1,
                    Number(values.day),
                    Number(values.hour),
                    Number(values.minute),
                    Number(values.second),
                );

                return Math.round((localAsUtc - timestamp) / 60000) * 60;
            },
            formatOffset(offset) {
                const sign = offset < 0 ? '-' : '+';
                const absoluteOffset = Math.abs(offset);
                const hours = String(Math.floor(absoluteOffset / 3600)).padStart(2, '0');
                const minutes = String(Math.floor((absoluteOffset % 3600) / 60)).padStart(2, '0');

                return `UTC${sign}${hours}:${minutes}`;
            },
            timezoneSamples(timezones) {
                const samples = timezones
                    .slice(0, 3)
                    .map(timezone => timezone.split('/').pop().replaceAll('_', ' '));

                if (timezones.length > 3) {
                    samples.push(`+${timezones.length - 3}`);
                }

                return samples.join(', ');
            },
        }"
        x-init="detectTimezone(); syncTimezone()"
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
                    @change="syncTimezone()"
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

            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.date') }}</label>
                <input
                    type="date"
                    name="date"
                    id="date"
                    min="{{ $minDate }}"
                    x-model="selectedDate"
                    @change="syncTimezone()"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-gray-900"
                >
            </div>

            <div x-show="hasTimezoneRegions()" x-cloak>
                <label for="patient_timezone" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('booking_timezone.region_label') }}
                </label>
                <select
                    name="patient_timezone"
                    id="patient_timezone"
                    x-model="selectedTimezone"
                    :required="hasTimezoneRegions()"
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-gray-900 @error('patient_timezone') border-red-500 @enderror"
                >
                    <option value="">{{ __('booking_timezone.region_placeholder') }}</option>
                    <template x-for="option in effectiveTimezoneOptions()" :key="option.timezone">
                        <option :value="option.timezone" x-text="option.label"></option>
                    </template>
                </select>

                <p class="mt-2 text-sm text-gray-600">{{ __('booking_timezone.region_help') }}</p>

                @error('patient_timezone')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <button type="submit" class="w-full mt-6 bg-indigo-600 text-white py-3 px-4 rounded-md font-semibold hover:bg-indigo-700 transition shadow-lg">
            {{ __('app.find_slots') }}
        </button>
    </form>
</div>
@endsection

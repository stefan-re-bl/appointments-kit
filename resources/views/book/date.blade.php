@extends('layouts.guest')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('dashboard') }}" class="mb-6 inline-flex items-center text-sm font-semibold text-umbralia-title hover:text-umbralia-title">&larr; {{ __('app.back') }}</a>

    @include('book.partials.stepper', ['currentStep' => 1])

    <div class="my-8 h-1 rounded-full bg-umbralia-accent"></div>
    
    <h1 class="mb-8 text-center text-3xl font-bold tracking-tight text-umbralia-title">{{ __('app.select_date') }}</h1>

    <form
        action="{{ route('book.store.date') }}"
        method="POST"
        x-data="{
            countryTimezones: @js($countryTimezones),
            selectedCountry: @js(old('patient_country', $patientCountry ?? '')),
            selectedDate: @js(old('date', $selectedDate)),
            selectedTimezone: @js(old('patient_timezone', $patientTimezone ?? '')),
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

                this.selectedTimezone = '';
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
        x-init="syncTimezone()"
    >
        @csrf
        <div class="space-y-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            <div>
                <label for="patient_country" class="mb-2 block text-sm font-medium text-slate-800">
                    {{ __('booking_timezone.country_label') }}
                </label>
                <select
                    name="patient_country"
                    id="patient_country"
                    x-ref="country"
                    x-model="selectedCountry"
                    @change="syncTimezone()"
                    required
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-umbralia-accent focus:ring-2 focus:ring-umbralia-accent/30 @error('patient_country') border-rose-300 @enderror"
                >
                    <option value="">{{ __('booking_timezone.country_placeholder') }}</option>
                    @foreach ($countries as $countryCode => $timezone)
                        <option value="{{ $countryCode }}" @selected(old('patient_country', $patientCountry) === $countryCode)>
                            {{ __('booking_timezone.countries.'.$countryCode) }}
                        </option>
                    @endforeach
                </select>

                <p class="mt-2 text-sm text-slate-600">{{ __('booking_timezone.patient_country_help') }}</p>

                @error('patient_country')
                    <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="date" class="mb-2 block text-sm font-medium text-slate-800">{{ __('app.date') }}</label>
                <input
                    type="date"
                    name="date"
                    id="date"
                    min="{{ $minDate }}"
                    x-model="selectedDate"
                    @change="syncTimezone()"
                    required
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-umbralia-accent focus:ring-2 focus:ring-umbralia-accent/30"
                >
            </div>

            <div x-show="hasTimezoneRegions()" x-cloak>
                <label for="patient_timezone" class="mb-2 block text-sm font-medium text-slate-800">
                    {{ __('booking_timezone.region_label') }}
                </label>
                <select
                    name="patient_timezone"
                    id="patient_timezone"
                    x-model="selectedTimezone"
                    :required="hasTimezoneRegions()"
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-umbralia-accent focus:ring-2 focus:ring-umbralia-accent/30 @error('patient_timezone') border-rose-300 @enderror"
                >
                    <option value="">{{ __('booking_timezone.region_placeholder') }}</option>
                    <template x-for="option in effectiveTimezoneOptions()" :key="option.timezone">
                        <option :value="option.timezone" x-text="option.label"></option>
                    </template>
                </select>

                <p class="mt-2 text-sm text-slate-600">{{ __('booking_timezone.region_help') }}</p>

                @error('patient_timezone')
                    <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <button type="submit" class="mt-6 w-full rounded-lg bg-umbralia-title px-4 py-3 font-semibold text-white shadow-sm transition hover:bg-umbralia-title/90 focus:outline-none focus:ring-2 focus:ring-umbralia-accent/30 focus:ring-offset-2">
            {{ __('app.find_slots') }}
        </button>
    </form>
</div>
@endsection

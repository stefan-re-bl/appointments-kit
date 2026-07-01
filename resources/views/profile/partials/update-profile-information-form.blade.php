<section>
    <header>
        <h2 class="text-lg font-medium text-slate-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-slate-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-slate-900">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-slate-600 hover:text-slate-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500/30">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-emerald-700">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @if ($user->therapist)
            @php
                $avatarPreviewUrl = $user->therapist->avatar_url
                    ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name);
            @endphp

            <div
                x-data="{
                    previewUrl: @js($avatarPreviewUrl),
                    updatePreview(event) {
                        const [file] = event.target.files;

                        if (! file) {
                            return;
                        }

                        this.previewUrl = URL.createObjectURL(file);
                    },
                }"
                class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <img
                        src="{{ $avatarPreviewUrl }}"
                        :src="previewUrl"
                        alt="{{ __('app.profile.avatar_preview_alt') }}"
                        class="h-24 w-24 rounded-full border border-slate-200 bg-white object-cover shadow-sm"
                    >

                    <div class="min-w-0 flex-1">
                        <x-input-label for="avatar" :value="__('app.profile.avatar_upload')" />
                        <input
                            id="avatar"
                            name="avatar"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="mt-2 block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-800"
                            x-on:change="updatePreview($event)"
                        >
                        <p class="mt-2 text-sm text-slate-600">{{ __('app.profile.avatar_upload_help') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                    </div>
                </div>
            </div>

            <div>
                <x-input-label for="bio" :value="__('app.profile.therapist_bio')" />
                <textarea
                    id="bio"
                    name="bio"
                    rows="5"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                >{{ old('bio', $user->therapist->bio) }}</textarea>
                <p class="mt-1 text-sm text-slate-600">{{ __('app.profile.therapist_bio_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('bio')" />
            </div>

            <div>
                <x-input-label for="specialties" :value="__('app.profile.specialties')" />
                <textarea
                    id="specialties"
                    name="specialties"
                    rows="4"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                >{{ old('specialties', $user->therapist->specialties) }}</textarea>
                <p class="mt-1 text-sm text-slate-600">{{ __('app.profile.specialties_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('specialties')" />
            </div>

            <div>
                <x-input-label for="therapeutic_approach" :value="__('app.profile.therapeutic_approach')" />
                <textarea
                    id="therapeutic_approach"
                    name="therapeutic_approach"
                    rows="4"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                >{{ old('therapeutic_approach', $user->therapist->therapeutic_approach) }}</textarea>
                <p class="mt-1 text-sm text-slate-600">{{ __('app.profile.therapeutic_approach_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('therapeutic_approach')" />
            </div>

            <div>
                <x-input-label for="google_meet_link" :value="__('app.profile.google_meet_link')" />
                <x-text-input id="google_meet_link" name="google_meet_link" type="url" class="mt-1 block w-full" :value="old('google_meet_link', $user->therapist->google_meet_link)" />
                <p class="mt-1 text-sm text-slate-600">{{ __('app.profile.google_meet_link_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('google_meet_link')" />
            </div>

            <div>
                <x-input-label for="payment_instructions" :value="__('app.profile.payment_instructions')" />
                <textarea
                    id="payment_instructions"
                    name="payment_instructions"
                    rows="4"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                >{{ old('payment_instructions', $user->therapist->payment_instructions) }}</textarea>
                <p class="mt-1 text-sm text-slate-600">{{ __('app.profile.payment_instructions_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('payment_instructions')" />
            </div>

            <div
                x-data="{
                    countryTimezones: @js($countryTimezones),
                    countryDefaults: @js($countries),
                    selectedCountry: @js(old('therapist_country', $therapistCountry)),
                    selectedTimezone: @js(old('therapist_timezone', $therapistTimezone)),
                    referenceDate: @js($timezoneReferenceDate),
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
                                    timezone: timezones.includes(this.selectedTimezone)
                                        ? this.selectedTimezone
                                        : (timezones.includes(this.countryDefaults[this.selectedCountry])
                                            ? this.countryDefaults[this.selectedCountry]
                                            : timezones[0]),
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

                        const defaultTimezone = this.countryDefaults[this.selectedCountry];
                        const defaultOption = options.find(option => option.timezones.includes(defaultTimezone));

                        this.selectedTimezone = defaultOption?.timezone ?? options[0]?.timezone ?? '';
                    },
                    offsetForTimezone(timezone) {
                        const timestamp = Date.parse(`${this.referenceDate}T12:00:00Z`);
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
                class="space-y-4 rounded-2xl border border-amber-200 bg-amber-50 p-4"
            >
                <div>
                    <x-input-label for="therapist_country" :value="__('app.profile.timezone_country')" />
                    <select
                        id="therapist_country"
                        name="therapist_country"
                        x-model="selectedCountry"
                        x-on:change="syncTimezone()"
                        class="mt-1 block w-full rounded-lg border-slate-300 text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                        required
                    >
                        @foreach ($countries as $countryCode => $timezone)
                            <option value="{{ $countryCode }}" @selected(old('therapist_country', $therapistCountry) === $countryCode)>
                                {{ __('booking_timezone.countries.'.$countryCode) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-slate-700">{{ __('app.profile.timezone_help') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('therapist_country')" />
                </div>

                <div x-show="hasTimezoneRegions()" x-cloak>
                    <x-input-label for="therapist_timezone" :value="__('app.profile.timezone_region')" />
                    <select
                        id="therapist_timezone"
                        name="therapist_timezone"
                        x-model="selectedTimezone"
                        x-bind:required="hasTimezoneRegions()"
                        class="mt-1 block w-full rounded-lg border-slate-300 text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                    >
                        <option value="">{{ __('booking_timezone.region_placeholder') }}</option>
                        <template x-for="option in effectiveTimezoneOptions()" :key="option.timezone">
                            <option :value="option.timezone" x-text="option.label"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-sm text-slate-700">{{ __('app.profile.timezone_region_help') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('therapist_timezone')" />
                </div>
            </div>

            <div>
                <x-input-label for="avatar_url" :value="__('app.profile.avatar_url')" />
                <x-text-input id="avatar_url" name="avatar_url" type="text" class="mt-1 block w-full" :value="old('avatar_url', $user->therapist->avatar_url)" />
                <p class="mt-1 text-sm text-slate-600">{{ __('app.profile.avatar_url_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('avatar_url')" />
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-slate-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

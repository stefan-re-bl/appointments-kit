<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <input
            id="timezone"
            type="hidden"
            name="timezone"
            value="{{ old('timezone', 'UTC') }}"
        >

        <x-input-error :messages="$errors->get('timezone')" class="mt-2" />

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-slate-600 hover:text-slate-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-accent/30" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        const timezoneInput = document.getElementById('timezone');

        if (timezoneInput !== null) {
            try {
                const detectedTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

                if (typeof detectedTimezone === 'string' && detectedTimezone.length > 0) {
                    timezoneInput.value = detectedTimezone;
                }
            } catch (error) {
                timezoneInput.value = timezoneInput.value || 'UTC';
            }
        }
    </script>
</x-guest-layout>
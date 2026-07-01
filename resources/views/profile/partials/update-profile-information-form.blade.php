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
                <x-input-label for="google_meet_link" :value="__('app.profile.google_meet_link')" />
                <x-text-input id="google_meet_link" name="google_meet_link" type="url" class="mt-1 block w-full" :value="old('google_meet_link', $user->therapist->google_meet_link)" />
                <p class="mt-1 text-sm text-slate-600">{{ __('app.profile.google_meet_link_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('google_meet_link')" />
            </div>

            <div>
                <x-input-label for="avatar_url" :value="__('app.profile.avatar_url')" />
                <x-text-input id="avatar_url" name="avatar_url" type="url" class="mt-1 block w-full" :value="old('avatar_url', $user->therapist->avatar_url)" />
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

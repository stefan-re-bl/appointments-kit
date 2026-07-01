@extends('layouts.admin', [
    'title' => __('app.admin.therapists.edit_title'),
    'header' => __('app.admin.therapists.edit_title'),
])

@section('content')
    <form
        method="POST"
        action="{{ route('admin.therapists.update', $therapist) }}"
        class="mx-auto max-w-3xl space-y-6"
    >
        @csrf
        @method('PATCH')

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <h2 class="text-base font-semibold text-slate-950">
                    {{ __('app.admin.therapists.account_section') }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('app.admin.therapists.account_section_help') }}
                </p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.therapists.name') }}
                    </label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $therapist->user->name) }}"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                        required
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.therapists.email') }}
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $therapist->user->email) }}"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                        required
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <h2 class="text-base font-semibold text-slate-950">
                    {{ __('app.admin.therapists.profile_section') }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('app.admin.therapists.profile_section_help') }}
                </p>
            </div>

            <div class="space-y-5">
                <div>
                    <label for="timezone" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.therapists.timezone') }}
                    </label>
                    <input
                        id="timezone"
                        name="timezone"
                        type="text"
                        value="{{ old('timezone', $therapist->timezone) }}"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                        required
                    >
                    @error('timezone')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="google_meet_link" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.therapists.google_meet_link') }}
                    </label>
                    <input
                        id="google_meet_link"
                        name="google_meet_link"
                        type="url"
                        value="{{ old('google_meet_link', $therapist->google_meet_link) }}"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                    >
                    @error('google_meet_link')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="avatar_url" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.therapists.avatar_url') }}
                    </label>
                    <input
                        id="avatar_url"
                        name="avatar_url"
                        type="url"
                        value="{{ old('avatar_url', $therapist->avatar_url) }}"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                    >
                    @error('avatar_url')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bio" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.therapists.bio') }}
                    </label>
                    <textarea
                        id="bio"
                        name="bio"
                        rows="5"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                    >{{ old('bio', $therapist->bio) }}</textarea>
                    @error('bio')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <input type="hidden" name="is_active" value="0">

                    <label class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', $therapist->is_active))
                            class="mt-1 rounded border-slate-300 text-indigo-700 focus:ring-indigo-500/30"
                        >
                        <span>
                            <span class="block text-sm font-medium text-slate-950">
                                {{ __('app.admin.therapists.is_active') }}
                            </span>
                            <span class="mt-1 block text-sm text-slate-500">
                                {{ __('app.admin.therapists.is_active_help') }}
                            </span>
                        </span>
                    </label>

                    @error('is_active')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <input type="hidden" name="is_approved" value="0">

                    <label class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            name="is_approved"
                            value="1"
                            @checked(old('is_approved', $therapist->is_approved))
                            class="mt-1 rounded border-slate-300 text-indigo-700 focus:ring-indigo-500/30"
                        >
                        <span>
                            <span class="block text-sm font-medium text-slate-950">
                                {{ __('app.admin.therapists.is_approved') }}
                            </span>
                            <span class="mt-1 block text-sm text-slate-500">
                                {{ __('app.admin.therapists.is_approved_help') }}
                            </span>
                        </span>
                    </label>

                    @error('is_approved')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a
                href="{{ route('admin.therapists.index') }}"
                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                {{ __('app.admin.common.cancel') }}
            </a>

            <button
                type="submit"
                class="rounded-xl bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800"
            >
                {{ __('app.admin.common.save') }}
            </button>
        </div>
    </form>
@endsection

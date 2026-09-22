@extends('layouts.admin', [
    'title' => __('app.admin.professionals.edit_title'),
    'header' => __('app.admin.professionals.edit_title'),
])

@section('content')
    @php
        $currencyLabels = __('app.session_type_management.currency_options');
        $currencyOptions = collect(config('booking.currencies.supported', ['ARS', 'USD']))
            ->mapWithKeys(fn (string $currency): array => [$currency => $currencyLabels[$currency] ?? $currency])
            ->all();
    @endphp

    <form
        method="POST"
        action="{{ route('admin.professionals.update', $professional) }}"
        class="mx-auto max-w-3xl space-y-6"
    >
        @csrf
        @method('PATCH')

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <h2 class="text-base font-semibold text-brand-title">
                    {{ __('app.admin.professionals.account_section') }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('app.admin.professionals.account_section_help') }}
                </p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.professionals.name') }}
                    </label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $professional->user->name) }}"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                        required
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.professionals.email') }}
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $professional->user->email) }}"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
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
                <h2 class="text-base font-semibold text-brand-title">
                    {{ __('app.admin.professionals.internal_price_section') }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('app.admin.professionals.internal_price_section_help') }}
                </p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="session_price" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.professionals.session_price') }}
                    </label>
                    <input
                        id="session_price"
                        name="session_price"
                        type="number"
                        step="0.01"
                        min="0"
                        value="{{ old('session_price', $defaultSessionType?->price ?? '0.00') }}"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                        required
                    >
                    @error('session_price')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="session_currency" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.professionals.session_currency') }}
                    </label>
                    <select
                        id="session_currency"
                        name="session_currency"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                        required
                    >
                        @foreach ($currencyOptions as $currency => $label)
                            <option
                                value="{{ $currency }}"
                                @selected(old('session_currency', $defaultSessionType?->currency ?? config('booking.currencies.default', 'ARS')) === $currency)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('session_currency')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <h2 class="text-base font-semibold text-brand-title">
                    {{ __('app.admin.professionals.profile_section') }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('app.admin.professionals.profile_section_help') }}
                </p>
            </div>

            <div class="space-y-5">
                <div>
                    <label for="timezone" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.professionals.timezone') }}
                    </label>
                    <input
                        id="timezone"
                        name="timezone"
                        type="text"
                        value="{{ old('timezone', $professional->timezone) }}"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                        required
                    >
                    @error('timezone')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="google_meet_link" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.professionals.google_meet_link') }}
                    </label>
                    <input
                        id="google_meet_link"
                        name="google_meet_link"
                        type="url"
                        value="{{ old('google_meet_link', $professional->google_meet_link) }}"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                    >
                    @error('google_meet_link')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="avatar_url" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.professionals.avatar_url') }}
                    </label>
                    <input
                        id="avatar_url"
                        name="avatar_url"
                        type="url"
                        value="{{ old('avatar_url', $professional->avatar_url) }}"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                    >
                    @error('avatar_url')
                        <p class="mt-1 text-sm text-rose-800">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bio" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ __('app.admin.professionals.bio') }}
                    </label>
                    <textarea
                        id="bio"
                        name="bio"
                        rows="5"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                    >{{ old('bio', $professional->bio) }}</textarea>
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
                            @checked(old('is_active', $professional->is_active))
                            class="mt-1 rounded border-slate-300 text-brand-title focus:ring-brand-accent/30"
                        >
                        <span>
                            <span class="block text-sm font-medium text-slate-950">
                                {{ __('app.admin.professionals.is_active') }}
                            </span>
                            <span class="mt-1 block text-sm text-slate-500">
                                {{ __('app.admin.professionals.is_active_help') }}
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
                            @checked(old('is_approved', $professional->is_approved))
                            class="mt-1 rounded border-slate-300 text-brand-title focus:ring-brand-accent/30"
                        >
                        <span>
                            <span class="block text-sm font-medium text-slate-950">
                                {{ __('app.admin.professionals.is_approved') }}
                            </span>
                            <span class="mt-1 block text-sm text-slate-500">
                                {{ __('app.admin.professionals.is_approved_help') }}
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
                href="{{ route('admin.professionals.index') }}"
                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                {{ __('app.admin.common.cancel') }}
            </a>

            <button
                type="submit"
                class="rounded-xl bg-brand-title px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-title/90"
            >
                {{ __('app.admin.common.save') }}
            </button>
        </div>
    </form>
@endsection

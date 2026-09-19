@extends('layouts.public')

@section('title', __('app.contact.meta_title'))
@section('meta_description', __('app.contact.meta_description'))

@section('content')
    <section class="bg-white py-16">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.8fr_1.2fr] lg:px-8">
            <div>
                <p class="text-sm font-semibold uppercase text-brand-title">{{ __('app.contact.eyebrow') }}</p>
                <h1 class="mt-3 text-3xl font-bold text-brand-title sm:text-4xl">{{ __('app.contact.title') }}</h1>
                <p class="mt-4 text-base leading-7 text-slate-600">{{ __('app.contact.description') }}</p>
                <div class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm font-semibold text-amber-950">{{ __('app.home.emergency.title') }}</p>
                    <p class="mt-1 text-sm leading-6 text-amber-800">{{ __('app.home.emergency.description') }}</p>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                @if (session('success'))
                    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <p class="font-semibold">{{ __('app.contact.validation_title') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="grid gap-5">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-800">{{ __('app.contact.fields.name') }}</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                        >
                        @error('name')
                            <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-800">{{ __('app.contact.fields.email') }}</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="inquiry_type" class="block text-sm font-semibold text-slate-800">{{ __('app.contact.fields.inquiry_type') }}</label>
                        <select
                            id="inquiry_type"
                            name="inquiry_type"
                            required
                            class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                        >
                            @foreach ($inquiryTypes as $inquiryType)
                                <option value="{{ $inquiryType->value }}" @selected(old('inquiry_type', request('type')) === $inquiryType->value)>
                                    {{ __('app.contact.inquiry_types.' . $inquiryType->value) }}
                                </option>
                            @endforeach
                        </select>
                        @error('inquiry_type')
                            <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="hidden" aria-hidden="true">
                        <label for="company">{{ __('app.contact.fields.company') }}</label>
                        <input id="company" name="company" type="text" tabindex="-1" autocomplete="off">
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-semibold text-slate-800">{{ __('app.contact.fields.message') }}</label>
                        <textarea
                            id="message"
                            name="message"
                            rows="7"
                            required
                            class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                        >{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" class="inline-flex rounded-lg bg-brand-title px-5 py-3 text-sm font-semibold text-white hover:bg-brand-title/90 focus:outline-none focus:ring-2 focus:ring-brand-accent/30 focus:ring-offset-2">
                            {{ __('app.contact.submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

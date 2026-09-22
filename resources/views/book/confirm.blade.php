@extends('layouts.guest')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('book.time') }}" class="mb-6 inline-flex items-center text-sm font-semibold text-brand-title hover:text-brand-title">&larr; {{ __('app.change_date') }}</a>

    @include('book.partials.stepper', ['currentStep' => 3])

    <div class="my-8 h-1 rounded-full bg-brand-accent"></div>

    <h1 class="mb-8 text-center text-3xl font-bold tracking-tight text-brand-title">{{ __('app.confirm_booking') }}</h1>

    <!-- Mensaje de Error General (del try/catch del controlador) -->
    @error('general')
        <div class="relative mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800" role="alert">
            <strong class="font-bold">{{ __('app.error') }}!</strong>
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    @enderror

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50 p-6">
            <h2 class="text-lg font-semibold text-brand-title">{{ __('app.booking_internal.summary_title') }}</h2>
            <p class="text-slate-600">{{ $professional->user->name }}</p>
        </div>

        <div class="space-y-4 p-6">
            <div class="flex justify-between gap-6">
                <span class="text-slate-500">{{ __('app.date') }}:</span>
                <span class="text-right font-medium text-slate-950">{{ \Carbon\Carbon::parse($dateLocal)->locale(app()->getLocale())->isoFormat('LL') }}</span>
            </div>

            <div class="flex justify-between gap-6">
                <span class="text-slate-500">{{ __('app.time') }}:</span>
                <span class="text-right font-medium text-slate-950">{{ $localTime }}</span>
            </div>

            <div class="flex justify-between gap-6">
                <span class="text-slate-500">{{ __('booking_timezone.country_label') }}:</span>
                <span class="text-right font-medium text-slate-950">
                    @if ($patientCountry && $patientTimezoneLabel)
                        {{ __('booking_timezone.location_summary', [
                            'country' => __('booking_timezone.countries.'.$patientCountry),
                            'region' => $patientTimezoneLabel,
                        ]) }}
                    @else
                        {{ $patientCountry ? __('booking_timezone.countries.'.$patientCountry) : $patientTimezone }}
                    @endif
                </span>
            </div>
        </div>
    </div>

    <form action="{{ route('book.store') }}" method="POST" class="mt-8">
        @csrf

        <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h3 class="mb-2 font-medium text-brand-title">{{ __('app.booking_internal.patient_details') }}</h3>

            <div>
                <label for="customer_name" class="mb-1 block text-sm font-medium text-slate-800">{{ __('app.name') }}</label>
                <input
                    type="text"
                    name="customer_name"
                    id="customer_name"
                    value="{{ old('customer_name', old('patient_name')) }}"
                    required
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-brand-accent focus:ring-brand-accent/30 @error('patient_name') border-rose-400 @enderror"
                >

                @error('patient_name')
                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="customer_email" class="mb-1 block text-sm font-medium text-slate-800">{{ __('app.email') }}</label>
                <input
                    type="email"
                    name="customer_email"
                    id="customer_email"
                    value="{{ old('customer_email', old('patient_email')) }}"
                    required
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-brand-accent focus:ring-brand-accent/30 @error('patient_email') border-rose-400 @enderror"
                >

                @error('patient_email')
                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="customer_phone" class="mb-1 block text-sm font-medium text-slate-800">{{ __('app.whatsapp.patient_phone') }}</label>
                <input
                    type="tel"
                    name="customer_phone"
                    id="customer_phone"
                    value="{{ old('customer_phone', old('patient_phone')) }}"
                    placeholder="{{ __('app.whatsapp.phone_placeholder') }}"
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-brand-accent focus:ring-brand-accent/30 @error('patient_phone') border-rose-400 @enderror"
                >
                <p class="mt-1 text-sm text-slate-600">{{ __('app.whatsapp.patient_phone_help') }}</p>

                @error('patient_phone')
                    <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="mt-6 space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <label class="flex gap-3 text-sm text-slate-600">
                <input
                    type="checkbox"
                    name="accepted_whatsapp_communications"
                    value="1"
                    @checked(old('accepted_whatsapp_communications'))
                    class="mt-1 rounded border-brand-accent text-slate-600 focus:ring-brand-accent/30"
                >
                <span>{{ __('app.whatsapp.patient_opt_in') }}</span>
            </label>
            @error('accepted_whatsapp_communications')
                <p class="text-sm text-rose-700">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="mt-6 flex w-full items-center justify-center rounded-lg bg-brand-title px-4 py-3 text-lg font-bold text-white shadow-sm transition hover:bg-brand-title/90 focus:outline-none focus:ring-2 focus:ring-brand-accent/30 focus:ring-offset-2">
            {{ __('app.booking_internal.confirm') }}
        </button>
    </form>
</div>

@endsection

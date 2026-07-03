@extends('layouts.guest')

@section('content')
<div x-data="{ 
    slots: [], 
    loading: true, 
    selectedStart: null,
    therapistId: '{{ $therapistId }}',
    bookingDate: '{{ $date }}',
    duration: '{{ $sessionType->duration_minutes }}',
    patientTimezone: @js($patientTimezone),
    
    fetchSlots() {
        const url = `/api/slots?therapist_id=${this.therapistId}&date=${this.bookingDate}&duration=${this.duration}&timezone=${encodeURIComponent(this.patientTimezone)}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                this.slots = data;
                this.loading = false;
            })
            .catch(err => {
                console.error('Error cargando horarios:', err);
                this.loading = false;
            });
    }
}" x-init="fetchSlots()" class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('book.date') }}" class="mb-6 inline-flex items-center text-sm font-semibold text-indigo-700 hover:text-indigo-800">&larr; {{ __('app.change_date') }}</a>

    @include('book.partials.stepper', ['currentStep' => 4])
    
    <h1 class="mb-2 text-center text-3xl font-bold tracking-tight text-slate-950">{{ __('app.select_time') }}</h1>
    <p class="mb-8 text-center text-slate-500">
        {{ \Carbon\Carbon::parse($date)->locale(app()->getLocale())->isoFormat('LL') }}
    </p>
    <p class="-mt-5 mb-8 text-center text-sm text-slate-600">
        @if ($patientCountry && $patientTimezoneLabel)
            {{ __('booking_timezone.showing_location', [
                'country' => __('booking_timezone.countries.'.$patientCountry),
                'region' => $patientTimezoneLabel,
            ]) }}
        @else
            {{ __('booking_timezone.showing_country', [
                'country' => $patientCountry ? __('booking_timezone.countries.'.$patientCountry) : $patientTimezone,
            ]) }}
        @endif
    </p>

    <div class="min-h-[200px] rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        
        <!-- Estado: Cargando -->
        <template x-if="loading">
            <div class="flex items-center justify-center py-12">
                <svg class="h-8 w-8 animate-spin text-indigo-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </template>

        <!-- Estado: Sin horarios -->
        <template x-if="!loading && slots.length === 0">
            <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-10 text-center text-slate-500">
                {{ __('app.no_slots_available') }}
            </p>
        </template>

        <!-- Estado: Lista de Horarios -->
        <form action="{{ route('book.store.time') }}" method="POST" x-show="!loading && slots.length > 0">
            @csrf
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 md:grid-cols-5">
                <template x-for="slot in slots" :key="slot.start_utc">
                    <label class="cursor-pointer">
                        <input type="radio" name="starts_at_utc" :value="slot.start_utc" x-model="selectedStart" class="peer sr-only" required>
                        <div class="rounded-lg border border-slate-200 bg-white px-2 py-3 text-center text-sm font-semibold text-slate-700 shadow-sm transition-all hover:border-indigo-300 hover:bg-indigo-50 peer-checked:border-indigo-700 peer-checked:bg-indigo-700 peer-checked:text-white">
                            <span x-text="slot.label"></span>
                        </div>
                    </label>
                </template>
            </div>

            <button type="submit" 
                    :disabled="!selectedStart"
                    class="mt-8 w-full rounded-lg bg-indigo-700 px-4 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-800 disabled:cursor-not-allowed disabled:opacity-50">
                {{ __('app.continue') }}
            </button>
        </form>
    </div>
</div>
@endsection

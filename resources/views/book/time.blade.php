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
}" x-init="fetchSlots()" class="max-w-3xl mx-auto">
    <a href="{{ route('book.date') }}" class="text-indigo-600 hover:text-indigo-800 text-sm mb-4 inline-block">&larr; {{ __('app.change_date') }}</a>
    
    <h1 class="text-3xl font-bold text-gray-900 mb-2 text-center">{{ __('app.select_time') }}</h1>
    <p class="text-center text-gray-500 mb-8">
        {{ \Carbon\Carbon::parse($date)->locale(app()->getLocale())->isoFormat('LL') }}
    </p>
    <p class="text-center text-sm text-gray-600 -mt-5 mb-8">
        {{ __('booking_timezone.showing', ['timezone' => $patientTimezone]) }}
    </p>

    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 min-h-[200px]">
        
        <!-- Estado: Cargando -->
        <template x-if="loading">
            <div class="flex justify-center items-center py-10">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </template>

        <!-- Estado: Sin horarios -->
        <template x-if="!loading && slots.length === 0">
            <p class="text-center text-gray-500 py-10">
                {{ __('app.no_slots_available') }}
            </p>
        </template>

        <!-- Estado: Lista de Horarios -->
        <form action="{{ route('book.store.time') }}" method="POST" x-show="!loading && slots.length > 0">
            @csrf
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                <template x-for="slot in slots" :key="slot.start_utc">
                    <label class="cursor-pointer">
                        <input type="radio" name="starts_at_utc" :value="slot.start_utc" x-model="selectedStart" class="peer sr-only" required>
                        <div class="text-center py-3 px-2 border rounded-md text-sm font-medium transition-all
                                    peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600
                                    hover:bg-gray-50 border-gray-300 text-gray-700">
                            <span x-text="slot.label"></span>
                        </div>
                    </label>
                </template>
            </div>

            <button type="submit" 
                    :disabled="!selectedStart"
                    class="w-full mt-8 bg-indigo-600 text-white py-3 px-4 rounded-md font-semibold hover:bg-indigo-700 transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                {{ __('app.continue') }}
            </button>
        </form>
    </div>
</div>
@endsection

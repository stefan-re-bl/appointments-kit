<x-app-layout>
    <x-slot:header>
        <h2 class="text-xl font-semibold leading-tight text-slate-900">
            {{ __('app.availability.create_title') }}
        </h2>
    </x-slot:header>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                
                <form action="{{ route('availabilities.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-6">
                        <label for="day_of_week" class="block text-sm font-medium text-slate-800">{{ __('app.availability.day_of_week') }}</label>
                        <select name="day_of_week" id="day_of_week" class="mt-1 block w-full rounded-lg border-slate-300 text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30" required>
                            @foreach($days as $num => $day)
                                <option value="{{ $num }}" {{ old('day_of_week') == $num ? 'selected' : '' }}>{{ $day }}</option>
                            @endforeach
                        </select>
                        @error('day_of_week') <p class="mt-2 text-sm text-rose-700">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ slots: [{start_time: '', end_time: '', is_active: true}] }">
                        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="text-md font-semibold text-slate-800">{{ __('app.availability.ranges') }}</h3>
                            <button type="button" @click="slots.push({start_time: '', end_time: '', is_active: true})" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                                {{ __('app.availability.add_range') }}
                            </button>
                        </div>

                        @error('slots') <p class="mb-4 mt-2 text-sm text-rose-700">{{ $message }}</p> @enderror

                        <template x-for="(slot, index) in slots" :key="index">
                            <div class="mb-4 grid gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-[1fr_1fr_auto_auto] sm:items-end">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-slate-600">{{ __('app.availability.start') }}</label>
                                    <input type="time" :name="'slots['+index+'][start_time]'" x-model="slot.start_time" class="mt-1 block w-full rounded-lg border-slate-300 text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30" required>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-slate-600">{{ __('app.availability.end') }}</label>
                                    <input type="time" :name="'slots['+index+'][end_time]'" x-model="slot.end_time" class="mt-1 block w-full rounded-lg border-slate-300 text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30" required>
                                </div>
                                <div class="flex flex-col">
                                    <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('app.active') }}</label>
                                    {{-- El hidden envía 0 si el checkbox está desmarcado. --}}
                                    <div class="relative">
                                        <input type="hidden" :name="'slots['+index+'][is_active]'" value="0">
                                        <input type="checkbox" :name="'slots['+index+'][is_active]'" value="1" x-model="slot.is_active" class="rounded border-slate-300 text-indigo-700 shadow-sm focus:ring-indigo-500/30">
                                    </div>
                                </div>
                                <button type="button" @click="slots.splice(index, 1)" class="text-xl font-bold text-rose-600 hover:text-rose-800" x-show="slots.length > 1">
                                    &times;
                                </button>
                            </div>
                        </template>

                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                            <a href="{{ route('availabilities.index') }}" class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('app.availability.cancel') }}</a>
                            <button type="submit" class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2">
                                {{ __('app.availability.save') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot:header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Añadir Rangos Horarios
        </h2>
    </x-slot:header>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('availabilities.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-6">
                        <label for="day_of_week" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Día de la semana</label>
                        <select name="day_of_week" id="day_of_week" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @foreach($days as $num => $day)
                                <option value="{{ $num }}" {{ old('day_of_week') == $num ? 'selected' : '' }}>{{ $day }}</option>
                            @endforeach
                        </select>
                        @error('day_of_week') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ slots: [{start_time: '', end_time: '', is_active: true}] }">
                        <div class="mb-4 flex justify-between items-center">
                            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-200">Rangos Horarios</h3>
                            <button type="button" @click="slots.push({start_time: '', end_time: '', is_active: true})" class="text-sm bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-bold py-1 px-3 rounded">
                                + Añadir Rango
                            </button>
                        </div>

                        @error('slots') <p class="mt-2 mb-4 text-sm text-red-600">{{ $message }}</p> @enderror

                        <template x-for="(slot, index) in slots" :key="index">
                            <div class="flex items-center gap-4 mb-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-600 dark:text-gray-400">Inicio</label>
                                    <input type="time" :name="'slots['+index+'][start_time]'" x-model="slot.start_time" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 shadow-sm" required>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-600 dark:text-gray-400">Fin</label>
                                    <input type="time" :name="'slots['+index+'][end_time]'" x-model="slot.end_time" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 shadow-sm" required>
                                </div>
                                <div class="flex flex-col items-center">
                                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">Activo</label>
                                    <!-- Truco input hidden: envía 0 si el checkbox está desmarcado -->
                                    <div class="relative">
                                        <input type="hidden" :name="'slots['+index+'][is_active]'" value="0">
                                        <input type="checkbox" :name="'slots['+index+'][is_active]'" value="1" x-model="slot.is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                    </div>
                                </div>
                                <button type="button" @click="slots.splice(index, 1)" class="text-red-500 hover:text-red-700 font-bold text-xl" x-show="slots.length > 1">
                                    &times;
                                </button>
                            </div>
                        </template>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('availabilities.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mr-4">Cancelar</a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Guardar Disponibilidad
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Crear Tipo de Sesión</h2>

                <form action="{{ route('session-types.store') }}" method="POST">
                    @csrf

                    <x-input name="name" label="Nombre del Tipo de Sesión" type="text" />

                    <div class="mt-4">
                        <x-select name="duration_minutes" label="Duración (minutos)" :options="['30' => '30 minutos', '60' => '60 minutos', '90' => '90 minutos']" />
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <x-input name="price" label="Precio" type="number" step="0.01" />
                        <x-select name="currency" label="Moneda" :options="['ARS' => 'Pesos Argentinos (ARS)', 'USD' => 'Dólares (USD)']" />
                    </div>

                    <div class="mt-6 flex items-center">
                        <!-- Alpine.js Toggle para is_active -->
                        <div x-data="{ active: true }" class="flex items-center">
                            <button type="button" @click="active = !active" 
                                    :class="active ? 'bg-indigo-600' : 'bg-gray-200'" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <span :class="active ? 'translate-x-5' : 'translate-x-0'" 
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                            <span class="ml-3 text-sm text-gray-600" x-text="active ? 'Activo' : 'Inactivo'"></span>
                            <input type="hidden" name="is_active" :value="active ? 1 : 0">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('session-types.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
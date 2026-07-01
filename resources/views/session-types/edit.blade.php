<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-6 text-2xl font-bold text-slate-900">Editar Tipo de Sesión</h2>

                <form action="{{ route('session-types.update', $sessionType) }}" method="POST">
                    @csrf @method('PUT')

                    <x-input name="name" label="Nombre del Tipo de Sesión" type="text" :value="old('name', $sessionType->name)" />

                    <div class="mt-4">
                        <x-select name="duration_minutes" label="Duración (minutos)" :options="['30' => '30 minutos', '60' => '60 minutos', '90' => '90 minutos']" :selected="old('duration_minutes', $sessionType->duration_minutes)" />
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <x-input name="price" label="Precio" type="number" step="0.01" :value="old('price', $sessionType->price)" />
                        <x-select name="currency" label="Moneda" :options="['ARS' => 'Pesos Argentinos (ARS)', 'USD' => 'Dólares (USD)']" :selected="old('currency', $sessionType->currency)" />
                    </div>

                    <div class="mt-6 flex items-center">
                        <!-- Alpine.js Toggle para is_active (inyectando valor de BD inicial) -->
                        <div x-data="{ active: {{ $sessionType->is_active ? 'true' : 'false' }} }" class="flex items-center">
                            <button type="button" @click="active = !active"
                                    :class="active ? 'bg-indigo-700' : 'bg-slate-200'"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2">
                                <span :class="active ? 'translate-x-5' : 'translate-x-0'"
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                            <span class="ml-3 text-sm text-slate-600" x-text="active ? 'Activo' : 'Inactivo'"></span>
                            <input type="hidden" name="is_active" :value="active ? 1 : 0">
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('session-types.index') }}" class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Cancelar</a>
                        <button type="submit" class="rounded-lg bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tipos de Sesión
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Botón para Crear -->
            <div class="flex justify-end">
                <a href="{{ route('session-types.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    + Crear Nuevo Tipo de Sesión
                </a>
            </div>

            <!-- Tabla de Tipos de Sesión -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded border border-green-300">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase">Nombre</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase">Duración</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase">Precio</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase">Estado</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessionTypes as $type)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 border-b">{{ $type->name }}</td>
                                <td class="py-3 px-4 border-b">{{ $type->duration_minutes }} min</td>
                                <td class="py-3 px-4 border-b">{{ $type->price }} {{ $type->currency }}</td>
                                <td class="py-3 px-4 border-b">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $type->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $type->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 border-b flex gap-3">
                                    <a href="{{ route('session-types.edit', $type) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Editar</a>
                                    <form action="{{ route('session-types.destroy', $type) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este tipo de sesión?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @if($sessionTypes->isEmpty())
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">No hay tipos de sesión configurados. ¡Crea uno nuevo!</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
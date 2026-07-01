<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-900">
            Tipos de Sesión
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            
            <!-- Botón para Crear -->
            <div class="flex justify-end">
                <a href="{{ route('session-types.create') }}" class="inline-flex items-center rounded-lg border border-transparent bg-indigo-700 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-sm transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2">
                    + Crear Nuevo Tipo de Sesión
                </a>
            </div>

            <!-- Tabla de Tipos de Sesión -->
            <div class="overflow-hidden rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">
                
                @if(session('success'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="space-y-3 md:hidden">
                    @forelse($sessionTypes as $type)
                        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold text-slate-900">{{ $type->name }}</h3>
                                    <p class="mt-1 text-sm text-slate-600">{{ $type->duration_minutes }} min · {{ $type->price }} {{ $type->currency }}</p>
                                </div>
                                <span class="shrink-0 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $type->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
                                    {{ $type->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-3 text-sm font-medium">
                                <a href="{{ route('session-types.edit', $type) }}" class="text-indigo-700 hover:text-indigo-800">Editar</a>
                                <form action="{{ route('session-types.destroy', $type) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este tipo de sesión?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-700 hover:text-rose-800">Eliminar</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-500">No hay tipos de sesión configurados. ¡Crea uno nuevo!</div>
                    @endforelse
                </div>

                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full border border-slate-200 bg-white">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">Nombre</th>
                                <th class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">Duración</th>
                                <th class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">Precio</th>
                                <th class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">Estado</th>
                                <th class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-sm text-slate-700">
                            @foreach($sessionTypes as $type)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $type->name }}</td>
                                <td class="px-4 py-3">{{ $type->duration_minutes }} min</td>
                                <td class="px-4 py-3">{{ $type->price }} {{ $type->currency }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $type->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
                                        {{ $type->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="flex gap-3 px-4 py-3">
                                    <a href="{{ route('session-types.edit', $type) }}" class="font-medium text-indigo-700 hover:text-indigo-800">Editar</a>
                                    <form action="{{ route('session-types.destroy', $type) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este tipo de sesión?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="font-medium text-rose-700 hover:text-rose-800">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @if($sessionTypes->isEmpty())
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">No hay tipos de sesión configurados. ¡Crea uno nuevo!</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

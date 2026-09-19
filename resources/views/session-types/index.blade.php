<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-900">
            {{ __('app.session_type_management.index_title') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            
            <!-- Botón para Crear -->
            <div class="flex justify-end">
                <a href="{{ route('session-types.create') }}" class="inline-flex items-center rounded-lg border border-transparent bg-brand-title px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-white shadow-sm transition hover:bg-brand-title/90 focus:outline-none focus:ring-2 focus:ring-brand-accent/30 focus:ring-offset-2">
                    {{ __('app.session_type_management.create_new') }}
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
                                    <p class="mt-1 text-sm text-slate-600">{{ $type->duration_minutes }} {{ __('app.session_type_management.minute_abbreviation') }} · {{ $type->price }} {{ $type->currency }}</p>
                                </div>
                                <span class="shrink-0 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $type->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
                                    {{ $type->is_active ? __('app.active') : __('app.inactive') }}
                                </span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-3 text-sm font-medium">
                                <a href="{{ route('session-types.edit', $type) }}" class="text-brand-title hover:text-brand-title">{{ __('app.edit') }}</a>
                                <form action="{{ route('session-types.destroy', $type) }}" method="POST" onsubmit="return confirm(@js(__('app.session_type_management.confirm_delete')));">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-700 hover:text-rose-800">{{ __('app.delete') }}</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-500">{{ __('app.session_type_management.empty') }}</div>
                    @endforelse
                </div>

                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full border border-slate-200 bg-white">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">{{ __('app.session_type_management.fields.name_short') }}</th>
                                <th class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">{{ __('app.session_type_management.fields.duration') }}</th>
                                <th class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">{{ __('app.session_type_management.fields.price') }}</th>
                                <th class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">{{ __('app.session_type_management.fields.status') }}</th>
                                <th class="border-b border-slate-200 px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600">{{ __('app.session_type_management.fields.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-sm text-slate-700">
                            @foreach($sessionTypes as $type)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $type->name }}</td>
                                <td class="px-4 py-3">{{ $type->duration_minutes }} {{ __('app.session_type_management.minute_abbreviation') }}</td>
                                <td class="px-4 py-3">{{ $type->price }} {{ $type->currency }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $type->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
                                        {{ $type->is_active ? __('app.active') : __('app.inactive') }}
                                    </span>
                                </td>
                                <td class="flex gap-3 px-4 py-3">
                                    <a href="{{ route('session-types.edit', $type) }}" class="font-medium text-brand-title hover:text-brand-title">{{ __('app.edit') }}</a>
                                    <form action="{{ route('session-types.destroy', $type) }}" method="POST" onsubmit="return confirm(@js(__('app.session_type_management.confirm_delete')));">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="font-medium text-rose-700 hover:text-rose-800">{{ __('app.delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @if($sessionTypes->isEmpty())
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">{{ __('app.session_type_management.empty') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

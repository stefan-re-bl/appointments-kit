<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-slate-900">
                {{ __('app.my_weekly_availability') }}
            </h2>
            <a href="{{ route('availabilities.create') }}" class="inline-flex justify-center rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2">
                {{ __('app.add_availability') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if(session('status'))
                <div class="relative rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($days as $num => $day)
                @if($availabilities->has($num))
                    <div class="overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                        {{-- Usamos el número del día ($num) para buscar la traducción dinámica --}}
                        <h3 class="mb-4 text-lg font-semibold text-slate-900">{{ __('app.day_' . $num) }}</h3>
                        <div class="space-y-2">
                            @foreach($availabilities[$num] as $avail)
                                <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono text-sm text-slate-800">{{ $avail->start_time_local }} - {{ $avail->end_time_local }}</span>
                                        <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $avail->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
                                            {{ $avail->is_active ? __('app.active') : __('app.inactive') }}
                                        </span>
                                    </div>
                                    <form action="{{ route('availabilities.destroy', $avail) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-rose-700 hover:text-rose-800">
                                            {{ __('app.delete') }}
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
            </div>

        </div>
    </div>
</x-app-layout>

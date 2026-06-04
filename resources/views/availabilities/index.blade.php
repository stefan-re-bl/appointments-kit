<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('app.my_weekly_availability') }}
            </h2>
            <a href="{{ route('availabilities.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                {{ __('app.add_availability') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            @foreach($days as $num => $day)
                @if($availabilities->has($num))
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        {{-- Usamos el número del día ($num) para buscar la traducción dinámica --}}
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('app.day_' . $num) }}</h3>
                        <div class="space-y-2">
                            @foreach($availabilities[$num] as $avail)
                                <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                    <div>
                                        <span class="font-mono text-gray-800 dark:text-gray-200">{{ $avail->start_time_local }} - {{ $avail->end_time_local }}</span>
                                        <span class="ml-2 text-xs px-2 py-1 rounded {{ $avail->is_active ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                            {{ $avail->is_active ? __('app.active') : __('app.inactive') }}
                                        </span>
                                    </div>
                                    <form action="{{ route('availabilities.destroy', $avail) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-bold">
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
</x-app-layout>
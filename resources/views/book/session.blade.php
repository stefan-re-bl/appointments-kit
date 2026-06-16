@extends('layouts.guest')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- CORRECCIÓN: book.index -->
    <a href="{{ route('book.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm mb-4 inline-block">&larr; {{ __('app.back') }}</a>
    
    <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">{{ __('app.select_session_type') }}</h1>

    <form action="{{ route('book.store.session') }}" method="POST">
        @csrf
        <div class="space-y-4">
            @foreach($sessions as $session)
                <label class="block relative cursor-pointer">
                    <input type="radio" name="session_type_id" value="{{ $session->id }}" class="peer sr-only" required>
                    <div class="p-6 rounded-lg border border-gray-200 bg-white peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:ring-1 peer-checked:ring-indigo-600 transition-all">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $session->name }}</h3>
                                <p class="text-gray-500 text-sm">{{ __('app.duration') }}: {{ $session->duration_minutes }} {{ __('app.minutes') }}</p>
                            </div>
                            <div class="text-xl font-bold text-gray-900">
                                {{ $session->price }} {{ $session->currency }}
                            </div>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>

        <div class="mt-8">
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-md font-semibold hover:bg-indigo-700 transition shadow-lg">
                {{ __('app.continue') }}
            </button>
        </div>
    </form>
</div>
@endsection
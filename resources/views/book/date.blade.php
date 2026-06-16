@extends('layouts.guest')

@section('content')
<div class="max-w-md mx-auto">
    <a href="{{ route('book.session') }}" class="text-indigo-600 hover:text-indigo-800 text-sm mb-4 inline-block">&larr; {{ __('app.back') }}</a>
    
    <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">{{ __('app.select_date') }}</h1>

    <form action="{{ route('book.store.date') }}" method="POST">
        @csrf
        <div class="bg-white p-8 rounded-lg shadow-md border border-gray-200">
            <label for="date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.date') }}</label>
            <input type="date" name="date" id="date" min="{{ $minDate }}" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-gray-900">
            
            <p class="mt-4 text-sm text-gray-500">
                {{ __('app.timezone_note', ['tz' => app('user.timezone')]) }}
            </p>
        </div>

        <button type="submit" class="w-full mt-6 bg-indigo-600 text-white py-3 px-4 rounded-md font-semibold hover:bg-indigo-700 transition shadow-lg">
            {{ __('app.find_slots') }}
        </button>
    </form>
</div>
@endsection
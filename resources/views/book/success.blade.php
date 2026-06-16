@extends('layouts.guest')

@section('content')
<div class="max-w-xl mx-auto text-center">
    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6">
        <svg class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('app.booking_confirmed') }}</h1>
    
    <p class="text-gray-600 mb-8 text-lg">
        {{ __('app.booking_success_message') }}
    </p>

    <div class="bg-blue-50 border border-blue-200 rounded-md p-6 mb-8 text-left">
        <h3 class="font-bold text-blue-900 mb-2">{{ __('app.next_steps') }}</h3>
        <ul class="list-disc list-inside text-blue-800 space-y-1 text-sm">
            <li>{{ __('app.step_check_email') }}</li>
            <li>{{ __('app.step_await_payment') }}</li>
            <li>{{ __('app.step_join_link') }}</li>
        </ul>
    </div>

    <a href="{{ route('book.index') }}" class="inline-block bg-indigo-600 text-white py-2 px-6 rounded-md hover:bg-indigo-700 transition">
        {{ __('app.book_another') }}
    </a>
</div>
@endsection
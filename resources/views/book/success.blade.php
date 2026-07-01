@extends('layouts.guest')

@section('content')
<div class="mx-auto max-w-xl text-center">
    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 ring-1 ring-emerald-200">
        <svg class="h-10 w-10 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h1 class="mb-4 text-3xl font-bold text-slate-950">{{ __('app.booking_confirmed') }}</h1>
    
    <p class="mb-8 text-lg text-slate-600">
        {{ __('app.booking_success_message') }}
    </p>

    <div class="mb-8 rounded-xl border border-slate-200 bg-slate-50 p-6 text-left">
        <h3 class="mb-2 font-bold text-slate-950">{{ __('app.next_steps') }}</h3>
        <ul class="list-inside list-disc space-y-1 text-sm text-slate-700">
            <li>{{ __('app.step_check_email') }}</li>
            <li>{{ __('app.step_await_payment') }}</li>
            <li>{{ __('app.step_join_link') }}</li>
        </ul>
    </div>

    <a href="{{ route('book.index') }}" class="inline-block rounded-lg bg-indigo-700 px-6 py-2.5 font-semibold text-white transition hover:bg-indigo-800">
        {{ __('app.book_another') }}
    </a>
</div>
@endsection

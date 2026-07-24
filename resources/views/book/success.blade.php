@extends('layouts.guest')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-8 text-center sm:px-6 lg:px-8">
    @include('book.partials.stepper', ['currentStep' => 6])

    <div class="my-8 h-1 rounded-full bg-umbralia-accent"></div>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 ring-1 ring-emerald-200">
            <svg class="h-10 w-10 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="mb-4 text-3xl font-bold tracking-tight text-umbralia-title">{{ __('app.booking_confirmed') }}</h1>

        <p class="mx-auto mb-8 max-w-xl text-lg leading-8 text-slate-600">
            {{ __('app.booking_success_message') }}
        </p>

        <div class="mb-8 rounded-xl border border-slate-200 bg-slate-50 p-5 text-left sm:p-6">
            <h3 class="mb-3 font-bold text-umbralia-title">{{ __('app.next_steps') }}</h3>
            <ul class="list-inside list-disc space-y-2 text-sm leading-6 text-slate-700">
                <li>{{ __('app.step_check_email') }}</li>
                <li>{{ __('app.step_await_payment') }}</li>
                <li>{{ __('app.step_join_link') }}</li>
            </ul>
        </div>

        <a href="{{ route('book.index') }}" class="inline-flex rounded-lg bg-umbralia-title px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-umbralia-title/90 focus:outline-none focus:ring-2 focus:ring-umbralia-accent/30 focus:ring-offset-2">
            {{ __('app.book_another') }}
        </a>
    </div>
</div>
@endsection

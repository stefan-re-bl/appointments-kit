@extends('layouts.guest')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('home') }}" class="mb-6 inline-flex items-center text-sm font-semibold text-umbralia-title hover:text-umbralia-title">&larr; {{ __('app.appointment_public.actions.back_home') }}</a>

    @include('book.partials.stepper', ['currentStep' => 1])

    <div class="my-8 h-1 rounded-full bg-umbralia-accent"></div>

    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold tracking-tight text-umbralia-title">{{ __('app.select_therapist') }}</h1>
    </div>
    
    <div class="grid gap-6 md:grid-cols-2">
        @foreach($therapists as $therapist)
            <form action="{{ route('book.store.therapist') }}" method="POST">
                @csrf
                <div class="group flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-umbralia-accent hover:shadow-md sm:p-6">
                    <input type="hidden" name="therapist_id" value="{{ $therapist->id }}">
                    
                    <div class="mb-4 flex items-center gap-4">
                        <img src="{{ $therapist->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($therapist->user->name) }}" 
                             alt="{{ $therapist->user->name }}" 
                             class="h-16 w-16 rounded-full border-4 border-white bg-white object-cover shadow-sm ring-1 ring-slate-200">
                        <div class="min-w-0">
                            <h2 class="truncate text-xl font-semibold text-umbralia-title group-hover:text-umbralia-title">{{ $therapist->user->name }}</h2>
                            <p class="truncate text-sm text-slate-500">{{ $therapist->timezone }}</p>
                        </div>
                    </div>
                    
                    @if($therapist->bio)
                        <p class="mb-5 line-clamp-3 text-sm leading-6 text-slate-600">{{ $therapist->bio }}</p>
                    @endif

                    <div class="mt-auto space-y-3">
                        <a
                            href="{{ route('therapists.show', $therapist->slug) }}"
                            class="flex w-full items-center justify-center rounded-lg bg-umbralia-accent px-4 py-3 font-semibold text-umbralia-title shadow-sm transition hover:bg-umbralia-accent-dark hover:text-white focus:outline-none focus:ring-2 focus:ring-umbralia-accent/30 focus:ring-offset-2"
                        >
                            {{ __('app.therapist_public.view_profile') }}
                        </a>

                        <button type="submit" class="w-full rounded-lg bg-umbralia-title px-4 py-3 font-semibold text-white shadow-sm transition hover:bg-umbralia-title/90 focus:outline-none focus:ring-2 focus:ring-umbralia-accent/30 focus:ring-offset-2">
                            {{ __('app.continue') }}
                        </button>
                    </div>
                </div>
            </form>
        @endforeach
    </div>
</div>
@endsection

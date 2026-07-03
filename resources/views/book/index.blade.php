@extends('layouts.guest')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    @include('book.partials.stepper', ['currentStep' => 1])

    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold tracking-tight text-slate-950">{{ __('app.select_therapist') }}</h1>
    </div>
    
    <div class="grid gap-6 md:grid-cols-2">
        @foreach($therapists as $therapist)
            <form action="{{ route('book.store.therapist') }}" method="POST">
                @csrf
                <div class="group flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md sm:p-6">
                    <input type="hidden" name="therapist_id" value="{{ $therapist->id }}">
                    
                    <div class="mb-4 flex items-center gap-4">
                        <img src="{{ $therapist->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($therapist->user->name) }}" 
                             alt="{{ $therapist->user->name }}" 
                             class="h-16 w-16 rounded-full border-4 border-white bg-white object-cover shadow-sm ring-1 ring-slate-200">
                        <div class="min-w-0">
                            <h2 class="truncate text-xl font-semibold text-slate-950 group-hover:text-indigo-700">{{ $therapist->user->name }}</h2>
                            <p class="truncate text-sm text-slate-500">{{ $therapist->timezone }}</p>
                        </div>
                    </div>
                    
                    @if($therapist->bio)
                        <p class="mb-5 line-clamp-3 text-sm leading-6 text-slate-600">{{ $therapist->bio }}</p>
                    @endif

                    <div class="mt-auto space-y-3">
                        <a
                            href="{{ route('therapists.show', $therapist->slug) }}"
                            class="inline-flex text-sm font-semibold text-indigo-700 hover:text-indigo-800"
                        >
                            {{ __('app.therapist_public.view_profile') }}
                        </a>

                        <button type="submit" class="w-full rounded-lg bg-indigo-700 px-4 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2">
                            {{ __('app.continue') }}
                        </button>
                    </div>
                </div>
            </form>
        @endforeach
    </div>
</div>
@endsection

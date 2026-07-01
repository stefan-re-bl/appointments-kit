@extends('layouts.guest')

@section('content')
<div class="mx-auto max-w-3xl">
    <h1 class="mb-8 text-center text-3xl font-bold text-slate-950">{{ __('app.select_therapist') }}</h1>
    
    <div class="grid gap-6 md:grid-cols-2">
        @foreach($therapists as $therapist)
            <form action="{{ route('book.store.therapist') }}" method="POST">
                @csrf
                <div class="group cursor-pointer rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                    <input type="hidden" name="therapist_id" value="{{ $therapist->id }}">
                    
                    <div class="mb-4 flex items-center space-x-4">
                        <img src="{{ $therapist->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($therapist->user->name) }}" 
                             alt="{{ $therapist->user->name }}" 
                             class="h-16 w-16 rounded-full border border-slate-200 object-cover">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-950 group-hover:text-slate-600">{{ $therapist->user->name }}</h2>
                            <p class="text-sm text-slate-500">{{ $therapist->timezone }}</p>
                        </div>
                    </div>
                    
                    @if($therapist->bio)
                        <p class="mb-4 line-clamp-3 text-sm text-slate-600">{{ $therapist->bio }}</p>
                    @endif

                    <a
                        href="{{ route('therapists.show', $therapist->slug) }}"
                        class="mb-3 inline-flex text-sm font-semibold text-indigo-700 hover:text-indigo-800"
                    >
                        {{ __('app.therapist_public.view_profile') }}
                    </a>

                    <button type="submit" class="w-full rounded-lg bg-indigo-700 px-4 py-2.5 font-semibold text-white transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2">
                        {{ __('app.continue') }}
                    </button>
                </div>
            </form>
        @endforeach
    </div>
</div>
@endsection

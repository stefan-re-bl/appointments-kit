@extends('layouts.guest')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">{{ __('app.select_therapist') }}</h1>
    
    <div class="grid gap-6 md:grid-cols-2">
        @foreach($therapists as $therapist)
            <form action="{{ route('book.store.therapist') }}" method="POST">
                @csrf
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:border-indigo-500 transition cursor-pointer group">
                    <input type="hidden" name="therapist_id" value="{{ $therapist->id }}">
                    
                    <div class="flex items-center space-x-4 mb-4">
                        <img src="{{ $therapist->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($therapist->user->name) }}" 
                             alt="{{ $therapist->user->name }}" 
                             class="w-16 h-16 rounded-full object-cover border border-gray-200">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 group-hover:text-indigo-600">{{ $therapist->user->name }}</h2>
                            <p class="text-sm text-gray-500">{{ $therapist->timezone }}</p>
                        </div>
                    </div>
                    
                    @if($therapist->bio)
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $therapist->bio }}</p>
                    @endif

                    <a
                        href="{{ route('therapists.show', $therapist->slug) }}"
                        class="mb-3 inline-flex text-sm font-semibold text-indigo-700 hover:text-indigo-900"
                    >
                        {{ __('app.therapist_public.view_profile') }}
                    </a>

                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition">
                        {{ __('app.continue') }}
                    </button>
                </div>
            </form>
        @endforeach
    </div>
</div>
@endsection

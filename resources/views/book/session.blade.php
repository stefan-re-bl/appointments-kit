@extends('layouts.guest')

@section('content')
<div class="mx-auto max-w-2xl">
    <!-- CORRECCIÓN: book.index -->
    <a href="{{ route('book.index') }}" class="mb-4 inline-block text-sm font-medium text-indigo-700 hover:text-indigo-800">&larr; {{ __('app.back') }}</a>
    
    <h1 class="mb-8 text-center text-3xl font-bold text-slate-950">{{ __('app.select_session_type') }}</h1>

    <form action="{{ route('book.store.session') }}" method="POST">
        @csrf
        <div class="space-y-4">
            @foreach($sessions as $session)
                <label class="relative block cursor-pointer">
                    <input type="radio" name="session_type_id" value="{{ $session->id }}" class="peer sr-only" required>
                    <div class="rounded-xl border border-slate-200 bg-white p-6 transition-all peer-checked:border-indigo-600 peer-checked:bg-slate-50 peer-checked:ring-1 peer-checked:ring-indigo-600">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-950">{{ $session->name }}</h3>
                                <p class="text-sm text-slate-500">{{ __('app.duration') }}: {{ $session->duration_minutes }} {{ __('app.minutes') }}</p>
                            </div>
                            <div class="text-xl font-bold text-slate-950">
                                {{ $session->price }} {{ $session->currency }}
                            </div>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>

        <div class="mt-8">
            <button type="submit" class="w-full rounded-lg bg-indigo-700 px-4 py-3 font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2">
                {{ __('app.continue') }}
            </button>
        </div>
    </form>
</div>
@endsection

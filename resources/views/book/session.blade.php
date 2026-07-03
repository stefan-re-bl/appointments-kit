@extends('layouts.guest')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('book.index') }}" class="mb-6 inline-flex items-center text-sm font-semibold text-indigo-700 hover:text-indigo-800">&larr; {{ __('app.back') }}</a>

    @include('book.partials.stepper', ['currentStep' => 2])
    
    <h1 class="mb-8 text-center text-3xl font-bold tracking-tight text-slate-950">{{ __('app.select_session_type') }}</h1>

    <form action="{{ route('book.store.session') }}" method="POST">
        @csrf
        <div class="space-y-4">
            @foreach($sessions as $session)
                <label class="relative block cursor-pointer">
                    <input type="radio" name="session_type_id" value="{{ $session->id }}" class="peer sr-only" required>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-indigo-300 peer-checked:border-indigo-700 peer-checked:bg-indigo-50 peer-checked:ring-1 peer-checked:ring-indigo-700 sm:p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-950">{{ $session->name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ __('app.duration') }}: {{ $session->duration_minutes }} {{ __('app.minutes') }}</p>
                            </div>
                            <div class="text-xl font-bold text-indigo-700">
                                {{ $session->price }} {{ $session->currency }}
                            </div>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>

        <div class="mt-8">
            <button type="submit" class="w-full rounded-lg bg-indigo-700 px-4 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2">
                {{ __('app.continue') }}
            </button>
        </div>
    </form>
</div>
@endsection

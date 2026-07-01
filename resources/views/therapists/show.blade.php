@extends('layouts.guest')

@section('title', __('app.therapist_public.meta_title', ['name' => $therapist->user->name]))

@section('content')
    <div class="bg-white">
        <section class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8 lg:py-16">
            <div>
                <a href="{{ route('book.index') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">
                    {{ __('app.therapist_public.back_to_booking') }}
                </a>

                <div class="mt-8 flex flex-col gap-6 sm:flex-row sm:items-start">
                    <img
                        src="{{ $therapist->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($therapist->user->name) }}"
                        alt="{{ $therapist->user->name }}"
                        class="h-28 w-28 rounded-full border border-gray-200 object-cover"
                    >

                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-indigo-700">
                            {{ __('app.therapist_public.eyebrow') }}
                        </p>
                        <h1 class="mt-2 text-4xl font-bold tracking-tight text-gray-950">
                            {{ $therapist->user->name }}
                        </h1>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200">
                                {{ __('app.therapist_public.online_modality') }}
                            </span>
                            <span class="rounded-full bg-gray-50 px-3 py-1 text-sm font-medium text-gray-700 ring-1 ring-gray-200">
                                {{ __('app.therapist_public.timezone', ['timezone' => $therapist->timezone]) }}
                            </span>
                        </div>
                    </div>
                </div>

                @if ($therapist->bio)
                    <div class="mt-10 max-w-3xl">
                        <h2 class="text-xl font-semibold text-gray-950">
                            {{ __('app.therapist_public.about_title') }}
                        </h2>
                        <p class="mt-3 whitespace-pre-line text-base leading-8 text-gray-700">
                            {{ $therapist->bio }}
                        </p>
                    </div>
                @endif

                @if ($therapist->specialties || $therapist->therapeutic_approach)
                    <div class="mt-10 grid gap-6 md:grid-cols-2">
                        @if ($therapist->specialties)
                            <section class="rounded-xl border border-gray-200 p-5">
                                <h2 class="text-lg font-semibold text-gray-950">
                                    {{ __('app.therapist_public.specialties_title') }}
                                </h2>
                                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-gray-700">
                                    {{ $therapist->specialties }}
                                </p>
                            </section>
                        @endif

                        @if ($therapist->therapeutic_approach)
                            <section class="rounded-xl border border-gray-200 p-5">
                                <h2 class="text-lg font-semibold text-gray-950">
                                    {{ __('app.therapist_public.approach_title') }}
                                </h2>
                                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-gray-700">
                                    {{ $therapist->therapeutic_approach }}
                                </p>
                            </section>
                        @endif
                    </div>
                @endif

                <div class="mt-10">
                    <h2 class="text-xl font-semibold text-gray-950">
                        {{ __('app.therapist_public.sessions_title') }}
                    </h2>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @forelse ($therapist->sessionTypes as $sessionType)
                            <article class="rounded-xl border border-gray-200 p-5">
                                <h3 class="font-semibold text-gray-950">{{ $sessionType->name }}</h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    {{ __('app.therapist_public.session_duration', ['minutes' => $sessionType->duration_minutes]) }}
                                </p>
                                <p class="mt-3 text-lg font-semibold text-gray-950">
                                    {{ $sessionType->currency }} {{ number_format((float) $sessionType->price, 2) }}
                                </p>
                            </article>
                        @empty
                            <p class="rounded-xl border border-gray-200 p-5 text-sm text-gray-600">
                                {{ __('app.therapist_public.no_sessions') }}
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="h-fit rounded-2xl border border-gray-200 bg-gray-50 p-6">
                <h2 class="text-lg font-semibold text-gray-950">
                    {{ __('app.therapist_public.booking_card_title') }}
                </h2>
                <p class="mt-2 text-sm leading-6 text-gray-600">
                    {{ __('app.therapist_public.booking_card_text') }}
                </p>

                <form method="POST" action="{{ route('book.store.therapist') }}" class="mt-6">
                    @csrf
                    <input type="hidden" name="therapist_id" value="{{ $therapist->id }}">

                    <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700">
                        {{ __('app.therapist_public.book_cta') }}
                    </button>
                </form>

                <p class="mt-4 text-xs leading-5 text-gray-500">
                    {{ __('app.therapist_public.payment_note') }}
                </p>
            </aside>
        </section>
    </div>
@endsection

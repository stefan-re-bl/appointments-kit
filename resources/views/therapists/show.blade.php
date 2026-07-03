@extends('layouts.guest')

@section('title', __('app.therapist_public.meta_title', ['name' => $therapist->user->name]))

@section('content')
    <div class="bg-[#fbf9fc]">
        <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
            <a href="{{ route('book.index') }}" class="inline-flex items-center text-sm font-semibold text-indigo-700 hover:text-indigo-900">
                <span aria-hidden="true" class="mr-2">&larr;</span>
                <span>
                    {{ __('app.therapist_public.back_to_booking') }}
                </span>
            </a>

            <div class="mt-6 grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
                <div class="space-y-6">
                    <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                        <div @class([
                            'bg-slate-50 px-5 py-6 sm:px-7',
                            'border-b border-slate-100' => filled($therapist->bio),
                        ])>
                            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                                <img
                                    src="{{ $therapist->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($therapist->user->name) }}"
                                    alt="{{ $therapist->user->name }}"
                                    class="h-28 w-28 rounded-full border-4 border-white bg-white object-cover shadow-sm ring-1 ring-slate-200 sm:h-32 sm:w-32"
                                >

                                <div class="min-w-0">
                                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-700">
                                        {{ __('app.therapist_public.eyebrow') }}
                                    </p>
                                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">
                                        {{ $therapist->user->name }}
                                    </h1>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200">
                                            {{ __('app.therapist_public.online_modality') }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-sm font-medium text-slate-700 ring-1 ring-slate-200">
                                            {{ __('app.therapist_public.timezone', ['timezone' => $therapist->timezone]) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($therapist->bio)
                            <div class="px-5 py-6 sm:px-7">
                                <h2 class="text-xl font-semibold text-slate-950">
                                    {{ __('app.therapist_public.about_title') }}
                                </h2>
                                <p class="mt-3 max-w-3xl whitespace-pre-line text-base leading-8 text-slate-700">
                                    {{ $therapist->bio }}
                                </p>
                            </div>
                        @endif
                    </section>

                    @if ($therapist->specialties || $therapist->therapeutic_approach)
                        <div class="grid gap-4 md:grid-cols-2">
                            @if ($therapist->specialties)
                                <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
                                    <h2 class="text-lg font-semibold text-slate-950">
                                        {{ __('app.therapist_public.specialties_title') }}
                                    </h2>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">
                                        {{ $therapist->specialties }}
                                    </p>
                                </section>
                            @endif

                            @if ($therapist->therapeutic_approach)
                                <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
                                    <h2 class="text-lg font-semibold text-slate-950">
                                        {{ __('app.therapist_public.approach_title') }}
                                    </h2>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">
                                        {{ $therapist->therapeutic_approach }}
                                    </p>
                                </section>
                            @endif
                        </div>
                    @endif

                    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-7">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <h2 class="text-xl font-semibold text-slate-950">
                                {{ __('app.therapist_public.sessions_title') }}
                            </h2>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            @forelse ($therapist->sessionTypes as $sessionType)
                                <article class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                                    <h3 class="font-semibold text-slate-950">{{ $sessionType->name }}</h3>
                                    <div class="mt-4 flex items-end justify-between gap-4">
                                        <p class="text-sm text-slate-600">
                                            {{ __('app.therapist_public.session_duration', ['minutes' => $sessionType->duration_minutes]) }}
                                        </p>
                                        <p class="text-lg font-semibold text-slate-950">
                                            {{ $sessionType->currency }} {{ number_format((float) $sessionType->price, 2) }}
                                        </p>
                                    </div>
                                </article>
                            @empty
                                <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm leading-6 text-slate-600 md:col-span-2">
                                    {{ __('app.therapist_public.no_sessions') }}
                                </p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <aside class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 lg:sticky lg:top-24 lg:p-6">
                    <h2 class="text-lg font-semibold text-slate-950">
                        {{ __('app.therapist_public.booking_card_title') }}
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ __('app.therapist_public.booking_card_text') }}
                    </p>

                    <form method="POST" action="{{ route('book.store.therapist') }}" class="mt-6">
                        @csrf
                        <input type="hidden" name="therapist_id" value="{{ $therapist->id }}">

                        <button type="submit" class="w-full rounded-lg bg-indigo-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2">
                            {{ __('app.therapist_public.book_cta') }}
                        </button>
                    </form>

                    <p class="mt-4 border-t border-slate-200 pt-4 text-xs leading-5 text-slate-500">
                        {{ __('app.therapist_public.payment_note') }}
                    </p>
                </aside>
            </div>
        </section>
    </div>
@endsection

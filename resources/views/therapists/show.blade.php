@extends('layouts.guest')

@section('title', __('app.therapist_public.meta_title', ['name' => $therapist->user->name]))

@section('content')
    <div class="bg-white" x-data="{ presentationVideoOpen: false }">
        <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
            <a href="{{ route('therapists.index') }}" class="inline-flex items-center text-sm font-semibold text-umbralia-title hover:text-umbralia-title">
                <span aria-hidden="true" class="mr-2">&larr;</span>
                <span>
                    {{ __('app.therapist_public.back_to_directory') }}
                </span>
            </a>

            <div class="mt-6 grid gap-8 lg:items-start">
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
                                    <p class="text-sm font-semibold uppercase tracking-wide text-umbralia-title">
                                        {{ __('app.therapist_public.eyebrow') }}
                                    </p>
                                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-umbralia-title sm:text-4xl">
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

                                    @if ($therapist->presentation_video_url)
                                        <button
                                            type="button"
                                            class="mt-5 inline-flex items-center justify-center gap-2 rounded-lg bg-umbralia-title px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-umbralia-title/90 focus:outline-none focus:ring-2 focus:ring-umbralia-accent/30 focus:ring-offset-2"
                                            @click="presentationVideoOpen = true; $nextTick(() => $refs.presentationVideo.play())"
                                        >
                                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M8 5v14l11-7z"></path>
                                            </svg>
                                            {{ __('app.therapist_public.presentation_video_button') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if ($therapist->bio)
                            <div class="px-5 py-6 sm:px-7">
                                <h2 class="text-xl font-semibold text-umbralia-title">
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
                                    <h2 class="text-lg font-semibold text-umbralia-title">
                                        {{ __('app.therapist_public.specialties_title') }}
                                    </h2>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">
                                        {{ $therapist->specialties }}
                                    </p>
                                </section>
                            @endif

                            @if ($therapist->therapeutic_approach)
                                <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6">
                                    <h2 class="text-lg font-semibold text-umbralia-title">
                                        {{ __('app.therapist_public.approach_title') }}
                                    </h2>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">
                                        {{ $therapist->therapeutic_approach }}
                                    </p>
                                </section>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </section>

        @if ($therapist->presentation_video_url)
            <div
                x-cloak
                x-show="presentationVideoOpen"
                x-transition.opacity
                class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/82 px-4 py-8"
                role="dialog"
                aria-modal="true"
                aria-labelledby="presentation-video-title"
                @keydown.escape.window="presentationVideoOpen = false; $refs.presentationVideo.pause()"
            >
                <div class="absolute inset-0" @click="presentationVideoOpen = false; $refs.presentationVideo.pause()"></div>

                <div class="relative flex max-h-[calc(100svh-4rem)] w-fit max-w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-lg bg-black shadow-2xl ring-1 ring-white/20">
                    <div class="flex items-center justify-between gap-4 bg-white px-4 py-3">
                        <h2 id="presentation-video-title" class="text-base font-semibold text-umbralia-title">
                            {{ __('app.therapist_public.presentation_video_title', ['name' => $therapist->user->name]) }}
                        </h2>
                        <button
                            type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-umbralia-accent/30"
                            @click="presentationVideoOpen = false; $refs.presentationVideo.pause()"
                            title="{{ __('app.therapist_public.presentation_video_close') }}"
                            aria-label="{{ __('app.therapist_public.presentation_video_close') }}"
                        >
                            <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"></path>
                            </svg>
                        </button>
                    </div>

                    <video
                        x-ref="presentationVideo"
                        class="max-h-[calc(100svh-8rem)] w-auto max-w-full bg-black"
                        src="{{ $therapist->presentation_video_url }}"
                        controls
                        playsinline
                    >
                        {{ __('app.therapist_public.presentation_video_fallback') }}
                    </video>
                </div>
            </div>
        @endif
    </div>
@endsection

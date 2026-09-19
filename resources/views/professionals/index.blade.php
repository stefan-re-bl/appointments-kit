@extends('layouts.public')

@section('title', __('app.professional_public.directory.meta_title'))
@section('meta_description', __('app.professional_public.directory.meta_description'))

@section('content')
    <div class="bg-white" x-data="{ presentationVideoUrl: null, presentationVideoTitle: '' }">
        <section class="bg-brand-title py-16 text-white sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase text-brand-accent">
                        {{ __('app.professional_public.directory.eyebrow') }}
                    </p>
                    <h1 class="mt-4 text-4xl font-bold leading-tight text-white sm:text-5xl">
                        {{ __('app.professional_public.directory.title') }}
                    </h1>
                    <p class="mt-5 text-lg leading-8 text-slate-100">
                        {{ __('app.professional_public.directory.description') }}
                    </p>
                </div>
            </div>
        </section>

        <section class="py-12 sm:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="space-y-8">
                    @forelse ($professionals as $professional)
                        <article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                            <div class="grid gap-0 lg:grid-cols-[320px_minmax(0,1fr)]">
                                <div class="bg-slate-50 px-5 py-6 sm:px-7">
                                    <div class="flex flex-col items-start gap-5">
                                        <img
                                            src="{{ $professional->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($professional->user->name) }}"
                                            alt="{{ $professional->user->name }}"
                                            class="h-28 w-28 rounded-full border-4 border-white bg-white object-cover shadow-sm ring-1 ring-slate-200 sm:h-32 sm:w-32"
                                        >

                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold uppercase tracking-wide text-brand-title">
                                                {{ __('app.professional_public.eyebrow') }}
                                            </p>
                                            <h2 class="mt-2 text-2xl font-bold tracking-tight text-brand-title sm:text-3xl">
                                                {{ $professional->user->name }}
                                            </h2>

                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200">
                                                    {{ __('app.professional_public.online_modality') }}
                                                </span>
                                                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-sm font-medium text-slate-700 ring-1 ring-slate-200">
                                                    {{ __('app.professional_public.timezone', ['timezone' => $professional->timezone]) }}
                                                </span>
                                            </div>

                                            @if ($professional->presentation_video_url)
                                                <button
                                                    type="button"
                                                    class="mt-5 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-title px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-title/90 focus:outline-none focus:ring-2 focus:ring-brand-accent/30 focus:ring-offset-2"
                                                    @click="presentationVideoUrl = @js($professional->presentation_video_url); presentationVideoTitle = @js(__('app.professional_public.presentation_video_title', ['name' => $professional->user->name])); $nextTick(() => $refs.presentationVideo.play())"
                                                >
                                                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M8 5v14l11-7z"></path>
                                                    </svg>
                                                    {{ __('app.professional_public.presentation_video_button') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-6 px-5 py-6 sm:px-7">
                                    @if ($professional->bio)
                                        <section>
                                            <h3 class="text-xl font-semibold text-brand-title">
                                                {{ __('app.professional_public.about_title') }}
                                            </h3>
                                            <p class="mt-3 whitespace-pre-line text-base leading-8 text-slate-700">
                                                {{ $professional->bio }}
                                            </p>
                                        </section>
                                    @endif

                                    @if ($professional->specialties || $professional->professional_approach)
                                        <div class="grid gap-4 md:grid-cols-2">
                                            @if ($professional->specialties)
                                                <section class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                                                    <h3 class="text-lg font-semibold text-brand-title">
                                                        {{ __('app.professional_public.specialties_title') }}
                                                    </h3>
                                                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">
                                                        {{ $professional->specialties }}
                                                    </p>
                                                </section>
                                            @endif

                                            @if ($professional->professional_approach)
                                                <section class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                                                    <h3 class="text-lg font-semibold text-brand-title">
                                                        {{ __('app.professional_public.approach_title') }}
                                                    </h3>
                                                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">
                                                        {{ $professional->professional_approach }}
                                                    </p>
                                                </section>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                            <p class="text-base leading-7 text-slate-600">
                                {{ __('app.professional_public.directory.empty') }}
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <div
            x-cloak
            x-show="presentationVideoUrl"
            x-transition.opacity
            class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/82 px-4 py-8"
            role="dialog"
            aria-modal="true"
            aria-labelledby="presentation-video-title"
            @keydown.escape.window="presentationVideoUrl = null; $refs.presentationVideo.pause()"
        >
            <div class="absolute inset-0" @click="presentationVideoUrl = null; $refs.presentationVideo.pause()"></div>

            <div class="relative flex max-h-[calc(100svh-4rem)] w-fit max-w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-lg bg-black shadow-2xl ring-1 ring-white/20">
                <div class="flex items-center justify-between gap-4 bg-white px-4 py-3">
                    <h2 id="presentation-video-title" class="text-base font-semibold text-brand-title" x-text="presentationVideoTitle"></h2>
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-accent/30"
                        @click="presentationVideoUrl = null; $refs.presentationVideo.pause()"
                        title="{{ __('app.professional_public.presentation_video_close') }}"
                        aria-label="{{ __('app.professional_public.presentation_video_close') }}"
                    >
                        <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"></path>
                        </svg>
                    </button>
                </div>

                <video
                    x-ref="presentationVideo"
                    class="max-h-[calc(100svh-8rem)] w-auto max-w-full bg-black"
                    :src="presentationVideoUrl"
                    controls
                    playsinline
                >
                    {{ __('app.professional_public.presentation_video_fallback') }}
                </video>
            </div>
        </div>
    </div>
@endsection

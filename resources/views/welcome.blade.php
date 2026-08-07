@extends('layouts.public')

@section('title', __('app.home.meta_title'))
@section('meta_description', __('app.home.meta_description'))

@section('content')
@php
    $orientationSessionUrl = 'https://wa.me/5491150501775?text=' . urlencode(__('app.home.orientation_whatsapp_message'));
@endphp

<section
    class="home-hero relative isolate flex items-center overflow-hidden bg-umbralia-title bg-cover bg-center"
    style="background-image: url('{{ asset('images/umbralia-home-hero-cover.jpeg') }}');"
>
    <div class="absolute inset-0 bg-umbralia-title/90 sm:bg-umbralia-title/75 lg:bg-umbralia-title/60"></div>

    <div class="relative mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase text-umbralia-accent">{{ __('app.home.hero.eyebrow') }}</p>
            <h1 class="mt-4 text-4xl font-bold leading-tight text-white sm:text-5xl lg:text-6xl">
                Umbralia
            </h1>
            <p class="mt-5 max-w-xl text-xl font-semibold leading-8 text-white sm:text-2xl">
                {{ __('app.home.hero.title') }}
            </p>
            <p class="mt-5 max-w-xl text-base leading-7 text-slate-100 sm:text-lg">
                {{ __('app.home.hero.description') }}
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a
                    href="{{ $orientationSessionUrl }}"
                    target="_blank"
                    rel="noopener"
                    class="rounded-lg bg-umbralia-accent px-6 py-3.5 text-center font-semibold text-umbralia-title shadow-lg shadow-slate-900/20 hover:bg-umbralia-accent-dark hover:text-white focus:outline-none focus:ring-2 focus:ring-umbralia-accent focus:ring-offset-2 focus:ring-offset-umbralia-title"
                >
                    {{ __('app.home.hero.primary_cta') }}
                </a>
                <a
                    href="{{ route('information.how-it-works') }}"
                    class="rounded-lg border border-umbralia-accent/70 bg-umbralia-accent/15 px-6 py-3.5 text-center font-semibold text-white hover:bg-umbralia-accent hover:text-umbralia-title"
                >
                    {{ __('app.home.hero.secondary_cta') }}
                </a>
            </div>

            <p class="mt-6 border-l-2 border-umbralia-accent pl-4 text-sm leading-6 text-slate-100">
                {{ __('app.home.hero.payment_note') }}
            </p>
        </div>
    </div>
</section>

<section class="border-b border-slate-200 bg-white" x-data="{ introVideoOpen: false }">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-5 rounded-lg border border-umbralia-accent/30 bg-umbralia-accent-soft px-5 py-5 shadow-sm sm:px-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-start gap-4">
                <span class="mt-0.5 flex h-11 w-11 flex-none items-center justify-center rounded-full bg-white text-umbralia-title shadow-sm ring-1 ring-umbralia-accent/40">
                    <svg aria-hidden="true" class="ml-0.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z"></path>
                    </svg>
                </span>
                <p class="text-lg font-semibold leading-7 text-umbralia-title">
                    {{ __('app.home.intro_video.prompt') }}
                </p>
            </div>

            <button
                type="button"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-umbralia-title px-5 py-3 text-sm font-semibold text-white shadow-md shadow-umbralia-title/20 transition hover:-translate-y-0.5 hover:bg-umbralia-title/90 focus:outline-none focus:ring-2 focus:ring-umbralia-accent/40 focus:ring-offset-2"
                @click="introVideoOpen = true; $nextTick(() => $refs.introVideo.play())"
            >
                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8 5v14l11-7z"></path>
                </svg>
                {{ __('app.home.intro_video.button') }}
            </button>
        </div>
    </div>

    <div
        x-cloak
        x-show="introVideoOpen"
        x-transition.opacity
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/82 px-4 py-8"
        role="dialog"
        aria-modal="true"
        aria-labelledby="intro-video-title"
        @keydown.escape.window="introVideoOpen = false; $refs.introVideo.pause()"
    >
        <div class="absolute inset-0" @click="introVideoOpen = false; $refs.introVideo.pause()"></div>

        <div class="relative flex max-h-[calc(100svh-4rem)] w-fit max-w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-lg bg-black shadow-2xl ring-1 ring-white/20">
            <div class="flex items-center justify-between gap-4 bg-white px-4 py-3">
                <h2 id="intro-video-title" class="text-base font-semibold text-umbralia-title">
                    {{ __('app.home.intro_video.title') }}
                </h2>
                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-umbralia-accent/30"
                    @click="introVideoOpen = false; $refs.introVideo.pause()"
                    title="{{ __('app.home.intro_video.close') }}"
                    aria-label="{{ __('app.home.intro_video.close') }}"
                >
                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"></path>
                    </svg>
                </button>
            </div>

            <video
                x-ref="introVideo"
                class="max-h-[calc(100svh-8rem)] w-auto max-w-full bg-black"
                src="{{ asset('videos/umbralia-intro.mp4') }}"
                controls
                playsinline
            ></video>
        </div>
    </div>
</section>

<section class="border-b border-rose-200 bg-rose-50">
    <div class="mx-auto flex max-w-7xl gap-4 px-4 py-5 sm:px-6 lg:px-8">
        <span class="mt-0.5 flex h-6 w-6 flex-none items-center justify-center rounded-full bg-rose-800 text-xs font-bold text-white">!</span>
        <div>
            <p class="font-semibold text-rose-950">{{ __('app.home.emergency.title') }}</p>
            <p class="mt-1 text-sm leading-6 text-rose-800">{{ __('app.home.emergency.description') }}</p>
        </div>
    </div>
</section>

<section id="how-it-works" class="scroll-mt-24 bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase text-slate-600">{{ __('app.home.how.eyebrow') }}</p>
            <h2 class="mt-3 text-3xl font-bold text-umbralia-title sm:text-4xl">{{ __('app.home.how.title') }}</h2>
            <p class="mt-4 text-lg leading-8 text-slate-600">{{ __('app.home.how.description') }}</p>
        </div>

        <ol class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-4">
            @foreach (__('app.home.how.steps') as $index => $step)
                <li class="border-t-2 border-umbralia-accent pt-6">
                    <span class="text-sm font-bold text-umbralia-accent-dark">0{{ $index + 1 }}</span>
                    <h3 class="mt-3 text-xl font-semibold text-umbralia-title">{{ $step['title'] }}</h3>
                    <p class="mt-3 leading-7 text-slate-600">{{ $step['description'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

<section id="benefits" class="scroll-mt-24 bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
            <div>
                <p class="text-sm font-semibold uppercase text-umbralia-accent-dark">{{ __('app.home.benefits.eyebrow') }}</p>
                <h2 class="mt-3 text-3xl font-bold text-umbralia-title sm:text-4xl">{{ __('app.home.benefits.title') }}</h2>
                <p class="mt-4 text-lg leading-8 text-slate-600">{{ __('app.home.benefits.description') }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach (__('app.home.benefits.items') as $item)
                    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="h-1 w-10 bg-umbralia-accent"></div>
                        <h3 class="mt-5 text-lg font-semibold text-umbralia-title">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $item['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section id="therapists" class="scroll-mt-24 bg-umbralia-title py-20 text-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_auto] lg:items-center lg:px-8">
        <div class="max-w-3xl">
            <p class="text-sm font-semibold uppercase text-umbralia-accent">{{ __('app.home.therapists.eyebrow') }}</p>
            <h2 class="mt-3 text-3xl font-bold text-umbralia-accent sm:text-4xl">{{ __('app.home.therapists.title') }}</h2>
            <p class="mt-4 text-lg leading-8 text-slate-100">{{ __('app.home.therapists.description') }}</p>
        </div>
        <a href="{{ route('therapists.index') }}" class="rounded-lg bg-white px-6 py-3.5 text-center font-semibold text-slate-950 hover:bg-slate-50">
            {{ __('app.home.therapists.cta') }}
        </a>
    </div>
</section>

<section id="faq" class="scroll-mt-24 bg-white py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase text-slate-600">{{ __('app.home.faq.eyebrow') }}</p>
            <h2 class="mt-3 text-3xl font-bold text-umbralia-title sm:text-4xl">{{ __('app.home.faq.title') }}</h2>
        </div>

        <div class="mt-12 divide-y divide-slate-200 border-y border-slate-200">
            @foreach (__('app.home.faq.items') as $item)
                <details class="group py-5">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-6 font-semibold text-slate-950">
                        <span>{{ $item['question'] }}</span>
                        <span class="text-xl text-slate-600 group-open:rotate-45">+</span>
                    </summary>
                    <p class="mt-3 max-w-3xl pr-10 leading-7 text-slate-600">{{ $item['answer'] }}</p>
                </details>
            @endforeach
        </div>

        <div class="mt-12 text-center">
            <div class="flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ $orientationSessionUrl }}" target="_blank" rel="noopener" class="inline-flex justify-center rounded-lg bg-umbralia-title px-6 py-3.5 font-semibold text-white hover:bg-umbralia-title/90">
                    {{ __('app.home.faq.cta') }}
                </a>
                <a href="{{ route('contact.create') }}" class="inline-flex justify-center rounded-lg border border-slate-200 bg-white px-6 py-3.5 font-semibold text-slate-800 hover:bg-slate-50">
                    {{ __('app.home.faq.contact_cta') }}
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@extends('layouts.public')

@section('title', __('app.home.meta_title'))
@section('meta_description', __('app.home.meta_description'))

@section('content')
<section
    class="home-hero relative isolate flex items-center overflow-hidden bg-indigo-950 bg-cover bg-center"
    style="background-image: url('{{ asset('images/umbralia-home-hero-indigo.png') }}');"
>
    <div class="absolute inset-0 bg-indigo-950/76 sm:bg-indigo-950/64 lg:bg-indigo-950/56"></div>

    <div class="relative mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase text-amber-200">{{ __('app.home.hero.eyebrow') }}</p>
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
                    href="{{ route('book.index') }}"
                    class="rounded-lg bg-indigo-700 px-6 py-3.5 text-center font-semibold text-white shadow-lg shadow-slate-900/20 hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:ring-offset-2 focus:ring-offset-indigo-950"
                >
                    {{ __('app.home.hero.primary_cta') }}
                </a>
                <a
                    href="{{ route('information.how-it-works') }}"
                    class="rounded-lg border border-white/50 bg-indigo-950/30 px-6 py-3.5 text-center font-semibold text-white hover:bg-white hover:text-slate-950"
                >
                    {{ __('app.home.hero.secondary_cta') }}
                </a>
            </div>

            <p class="mt-6 border-l-2 border-amber-200 pl-4 text-sm leading-6 text-slate-100">
                {{ __('app.home.hero.payment_note') }}
            </p>
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
            <h2 class="mt-3 text-3xl font-bold text-slate-950 sm:text-4xl">{{ __('app.home.how.title') }}</h2>
            <p class="mt-4 text-lg leading-8 text-slate-600">{{ __('app.home.how.description') }}</p>
        </div>

        <ol class="mt-12 grid gap-8 md:grid-cols-3">
            @foreach (__('app.home.how.steps') as $index => $step)
                <li class="border-t-2 border-indigo-700 pt-6">
                    <span class="text-sm font-bold text-amber-700">0{{ $index + 1 }}</span>
                    <h3 class="mt-3 text-xl font-semibold text-slate-950">{{ $step['title'] }}</h3>
                    <p class="mt-3 leading-7 text-slate-600">{{ $step['description'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

<section id="benefits" class="scroll-mt-24 bg-slate-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
            <div>
                <p class="text-sm font-semibold uppercase text-amber-700">{{ __('app.home.benefits.eyebrow') }}</p>
                <h2 class="mt-3 text-3xl font-bold text-slate-950 sm:text-4xl">{{ __('app.home.benefits.title') }}</h2>
                <p class="mt-4 text-lg leading-8 text-slate-600">{{ __('app.home.benefits.description') }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach (__('app.home.benefits.items') as $item)
                    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="h-1 w-10 bg-amber-300"></div>
                        <h3 class="mt-5 text-lg font-semibold text-slate-950">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $item['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section id="therapists" class="scroll-mt-24 bg-indigo-950 py-20 text-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_auto] lg:items-center lg:px-8">
        <div class="max-w-3xl">
            <p class="text-sm font-semibold uppercase text-amber-200">{{ __('app.home.therapists.eyebrow') }}</p>
            <h2 class="mt-3 text-3xl font-bold sm:text-4xl">{{ __('app.home.therapists.title') }}</h2>
            <p class="mt-4 text-lg leading-8 text-slate-100">{{ __('app.home.therapists.description') }}</p>
        </div>
        <a href="{{ route('book.index') }}" class="rounded-lg bg-white px-6 py-3.5 text-center font-semibold text-slate-950 hover:bg-slate-50">
            {{ __('app.home.therapists.cta') }}
        </a>
    </div>
</section>

<section id="faq" class="scroll-mt-24 bg-white py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase text-slate-600">{{ __('app.home.faq.eyebrow') }}</p>
            <h2 class="mt-3 text-3xl font-bold text-slate-950 sm:text-4xl">{{ __('app.home.faq.title') }}</h2>
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
                <a href="{{ route('book.index') }}" class="inline-flex justify-center rounded-lg bg-indigo-700 px-6 py-3.5 font-semibold text-white hover:bg-indigo-800">
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

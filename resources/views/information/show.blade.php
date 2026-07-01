@php
    $content = __('information.pages.' . $page);
@endphp

@extends('layouts.public')

@section('title', $content['meta_title'])
@section('meta_description', $content['meta_description'])

@section('content')
<section class="relative isolate overflow-hidden bg-indigo-950 bg-cover bg-center" style="background-image: url('{{ asset('images/umbralia-home-hero.webp') }}');">
    <div class="absolute inset-0 bg-indigo-950/76"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
        <p class="text-sm font-semibold uppercase text-amber-200">{{ $content['eyebrow'] }}</p>
        <h1 class="mt-4 max-w-4xl text-4xl font-bold leading-tight text-white sm:text-5xl">{{ $content['title'] }}</h1>
        <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-100">{{ $content['intro'] }}</p>
    </div>
</section>

<div class="border-b border-slate-200 bg-slate-50">
    <nav class="public-section-nav mx-auto flex max-w-7xl gap-5 overflow-x-auto px-4 py-4 text-sm font-semibold text-slate-600 sm:px-6 lg:px-8" aria-label="{{ __('information.section_navigation') }}">
        <a href="{{ route('information.how-it-works') }}" class="whitespace-nowrap hover:text-slate-950">{{ __('app.home.nav.how_it_works') }}</a>
        <a href="{{ route('information.patients') }}" class="whitespace-nowrap hover:text-slate-950">{{ __('information.nav.patients') }}</a>
        <a href="{{ route('information.payment-and-cancellation') }}" class="whitespace-nowrap hover:text-slate-950">{{ __('information.nav.payment_and_cancellation') }}</a>
        <a href="{{ route('information.faq') }}" class="whitespace-nowrap hover:text-slate-950">{{ __('app.home.nav.faq') }}</a>
    </nav>
</div>

<section class="bg-white py-16 sm:py-20">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        @if ($page === 'how_it_works')
            <ol class="grid gap-8 md:grid-cols-2">
                @foreach ($content['sections'] as $index => $section)
                    <li class="border-t-2 border-indigo-700 pt-6">
                        <span class="text-sm font-bold text-amber-700">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <h2 class="mt-3 text-2xl font-semibold text-slate-950">{{ $section['title'] }}</h2>
                        <p class="mt-3 leading-7 text-slate-600">{{ $section['description'] }}</p>
                    </li>
                @endforeach
            </ol>
        @elseif ($page === 'faq')
            <div class="divide-y divide-slate-200 border-y border-slate-200">
                @foreach ($content['sections'] as $section)
                    <details class="group py-6">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-6 text-lg font-semibold text-slate-950">
                            <span>{{ $section['title'] }}</span>
                            <span class="text-2xl font-normal text-slate-600 group-open:rotate-45">+</span>
                        </summary>
                        <p class="mt-3 max-w-3xl pr-10 leading-7 text-slate-600">{{ $section['description'] }}</p>
                    </details>
                @endforeach
            </div>
        @else
            <div class="space-y-14">
                @foreach ($content['sections'] as $section)
                    <section class="grid gap-5 border-b border-slate-200 pb-12 last:border-0 last:pb-0 md:grid-cols-[0.8fr_1.2fr]">
                        <h2 class="text-2xl font-semibold text-slate-950">{{ $section['title'] }}</h2>
                        <div>
                            <p class="leading-7 text-slate-600">{{ $section['description'] }}</p>
                            @isset($section['items'])
                                <ul class="mt-5 space-y-3">
                                    @foreach ($section['items'] as $item)
                                        <li class="flex gap-3 text-sm leading-6 text-slate-600">
                                            <span class="mt-1 flex h-5 w-5 flex-none items-center justify-center rounded-full bg-indigo-50 text-xs font-bold text-slate-700">✓</span>
                                            <span>{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endisset
                        </div>
                    </section>
                @endforeach
            </div>
        @endif

        @isset($content['notice'])
            <aside class="mt-14 border-l-4 border-amber-500 bg-rose-50 px-6 py-5">
                <h2 class="font-semibold text-slate-950">{{ $content['notice']['title'] }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $content['notice']['description'] }}</p>
            </aside>
        @endisset

        <div class="mt-14 flex flex-col items-start justify-between gap-6 border-t border-slate-200 pt-10 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-xl font-semibold text-slate-950">{{ $content['cta']['title'] }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $content['cta']['description'] }}</p>
            </div>
            <a href="{{ route('book.index') }}" class="w-full flex-none rounded-lg bg-indigo-700 px-6 py-3 text-center font-semibold text-white hover:bg-indigo-800 sm:w-auto">{{ $content['cta']['label'] }}</a>
        </div>
    </div>
</section>
@endsection

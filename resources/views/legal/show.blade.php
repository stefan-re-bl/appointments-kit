@php
    $content = __('legal.pages.' . $page);
@endphp

@extends('layouts.public')

@section('title', $content['meta_title'])
@section('meta_description', $content['meta_description'])

@section('content')
<section class="bg-umbralia-title py-16 text-white sm:py-20">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase text-umbralia-accent">{{ __('legal.eyebrow') }}</p>
        <h1 class="mt-4 text-4xl font-bold sm:text-5xl">{{ $content['title'] }}</h1>
        <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-100">{{ $content['intro'] }}</p>
    </div>
</section>

<section class="bg-white py-16 sm:py-20">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-4 border-b border-slate-200 pb-8 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('legal.index') }}" class="text-sm font-semibold text-umbralia-title hover:text-umbralia-title">{{ __('legal.nav.index') }}</a>
            <a href="{{ route('legal.terms') }}" class="text-sm font-semibold text-umbralia-title hover:text-umbralia-title">{{ __('legal.nav.terms') }}</a>
            <a href="{{ route('legal.privacy') }}" class="text-sm font-semibold text-umbralia-title hover:text-umbralia-title">{{ __('legal.nav.privacy') }}</a>
            <a href="{{ route('legal.emergency-notice') }}" class="text-sm font-semibold text-umbralia-title hover:text-umbralia-title">{{ __('legal.nav.emergency') }}</a>
        </div>

        <div class="mt-10 space-y-12">
            @foreach ($content['sections'] as $section)
                <section class="grid gap-5 md:grid-cols-[0.8fr_1.2fr]">
                    <h2 class="text-2xl font-semibold text-umbralia-title">{{ $section['title'] }}</h2>
                    <div>
                        <p class="leading-7 text-slate-600">{{ $section['description'] }}</p>
                        @isset($section['items'])
                            <ul class="mt-5 space-y-3">
                                @foreach ($section['items'] as $item)
                                    <li class="flex gap-3 text-sm leading-6 text-slate-600">
                                        <span class="mt-1 flex h-5 w-5 flex-none items-center justify-center rounded-full bg-umbralia-accent-soft text-xs font-bold text-slate-700">✓</span>
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endisset
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</section>
@endsection

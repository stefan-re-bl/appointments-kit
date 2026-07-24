@extends('layouts.guest')

@section('title', __('app.therapist_public.not_found.title'))

@section('content')
    <div class="mx-auto flex min-h-[calc(100vh-13rem)] max-w-2xl flex-col items-center justify-center px-4 py-16 text-center sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-umbralia-title">
                {{ __('app.therapist_public.not_found.eyebrow') }}
            </p>
            <h1 class="mt-3 text-3xl font-bold text-gray-950">
                {{ __('app.therapist_public.not_found.title') }}
            </h1>
            <p class="mt-4 text-sm leading-6 text-gray-600">
                {{ __('app.therapist_public.not_found.message') }}
            </p>
            <a href="{{ route('book.index') }}" class="mt-6 inline-flex rounded-lg bg-umbralia-title px-4 py-2 text-sm font-semibold text-white hover:bg-umbralia-title">
                {{ __('app.therapist_public.not_found.cta') }}
            </a>
        </div>
    </div>
@endsection

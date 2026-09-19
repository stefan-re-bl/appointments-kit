<?php ?>

@extends('layouts.guest')

@section('title', __('app.appointment_public.not_found_title'))

@section('content')
    <div class="min-h-screen bg-white py-10">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-slate-200">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-accent-soft">
                    <span class="text-xl font-bold text-slate-600">
                        {{ __('app.appointment_public.not_found_icon') }}
                    </span>
                </div>

                <h1 class="mt-6 text-2xl font-bold tracking-tight text-brand-title">
                    {{ __('app.appointment_public.not_found_title') }}
                </h1>

                <p class="mt-3 text-sm leading-6 text-slate-600">
                    {{ __('app.appointment_public.not_found_message') }}
                </p>

                <a
                    href="{{ url('/') }}"
                    class="mt-6 inline-flex items-center justify-center rounded-lg bg-brand-title px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-title/90"
                >
                    {{ __('app.appointment_public.actions.back_home') }}
                </a>
            </div>
        </div>
    </div>
@endsection

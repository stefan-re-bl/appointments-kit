<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('app.dashboard') }}
        </h2>
    </x-slot>

    @php
        $approvalKey = 'pending';

        if ($professional && ! $professional->is_active) {
            $approvalKey = 'inactive';
        } elseif ($professional && $professional->is_approved) {
            $approvalKey = 'approved';
        }

        $checklist = $professional ? [
            'profile' => filled($professional->bio),
            'meet_link' => filled($professional->google_meet_link),
            'availability' => (int) $professional->active_availabilities_count > 0,
            'approval' => $professional->is_approved,
        ] : [];
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('warning'))
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
                    {{ session('warning') }}
                </div>
            @endif

            @if (! $professional)
                <section class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <p class="text-sm text-gray-700">{{ __('app.dashboard_onboarding.no_professional') }}</p>
                </section>
            @else
                <section class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wide text-brand-title">
                                {{ __('app.dashboard_onboarding.eyebrow') }}
                            </p>
                            <h3 class="mt-2 text-2xl font-semibold text-gray-950">
                                {{ __('app.dashboard_onboarding.title') }}
                            </h3>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600">
                                {{ __('app.dashboard_onboarding.subtitle') }}
                            </p>
                        </div>

                        <span @class([
                            'inline-flex rounded-full px-3 py-1 text-sm font-semibold',
                            'bg-amber-50 text-amber-800 ring-1 ring-amber-200' => $approvalKey === 'pending',
                            'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200' => $approvalKey === 'approved',
                            'bg-rose-50 text-rose-800 ring-1 ring-rose-200' => $approvalKey === 'inactive',
                        ])>
                            {{ __('app.dashboard_onboarding.status.' . $approvalKey) }}
                        </span>
                    </div>

                    <div class="mt-6">
                        <a
                            href="{{ route('book.index') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-brand-title px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-title/90 focus:outline-none focus:ring-2 focus:ring-brand-accent/30 focus:ring-offset-2"
                        >
                            {{ __('app.dashboard_onboarding.add_appointment') }}
                        </a>
                    </div>

                    @if (! $professional->is_approved)
                        <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            <p class="font-semibold">{{ __('app.dashboard_approval.pending_title') }}</p>
                            <p class="mt-1">{{ __('app.dashboard_approval.pending_message') }}</p>
                        </div>
                    @endif
                </section>

                <section class="grid gap-6 lg:grid-cols-[1.3fr_1fr]">
                    <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-950">
                            {{ __('app.dashboard_onboarding.flow_title') }}
                        </h3>

                        <ol class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach (__('app.dashboard_onboarding.flow_steps') as $step)
                                <li class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm font-semibold text-gray-950">{{ $step['title'] }}</p>
                                    <p class="mt-1 text-sm leading-6 text-gray-600">{{ $step['description'] }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-950">
                            {{ __('app.dashboard_onboarding.checklist_title') }}
                        </h3>

                        <ul class="mt-5 space-y-3">
                            @foreach ($checklist as $key => $complete)
                                <li class="flex items-start gap-3">
                                    <span @class([
                                        'mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold',
                                        'bg-emerald-100 text-emerald-800' => $complete,
                                        'bg-gray-100 text-gray-500' => ! $complete,
                                    ])>
                                        {{ $complete ? __('app.dashboard_onboarding.complete_icon') : __('app.dashboard_onboarding.pending_icon') }}
                                    </span>
                                    <span class="text-sm text-gray-700">
                                        {{ __('app.dashboard_onboarding.checklist.' . $key) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>

                <section class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-950">
                        {{ __('app.dashboard_onboarding.quick_links_title') }}
                    </h3>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <a href="{{ route('profile.edit') }}" class="rounded-lg border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-800 hover:border-brand-accent hover:bg-brand-accent-soft">
                            {{ __('app.dashboard_onboarding.quick_links.profile') }}
                        </a>
                        <a href="{{ route('availabilities.index') }}" class="rounded-lg border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-800 hover:border-brand-accent hover:bg-brand-accent-soft">
                            {{ __('app.dashboard_onboarding.quick_links.availability') }}
                        </a>
                        <a href="{{ route('professional.appointments.index') }}" class="rounded-lg border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-800 hover:border-brand-accent hover:bg-brand-accent-soft">
                            {{ __('app.dashboard_onboarding.quick_links.appointments') }}
                        </a>
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>

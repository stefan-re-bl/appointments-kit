@extends('layouts.admin', [
    'title' => __('app.admin.therapists.title'),
    'header' => __('app.admin.therapists.title'),
])

@section('content')
    <div class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.therapists.index') }}" class="flex flex-col gap-3 lg:flex-row">
                <div class="flex-1">
                    <label for="search" class="sr-only">{{ __('app.admin.therapists.search') }}</label>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="{{ __('app.admin.therapists.search_placeholder') }}"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                    >
                </div>

                <div class="lg:w-64">
                    <label for="approval_status" class="sr-only">{{ __('app.admin.therapists.approval_filter') }}</label>
                    <select
                        id="approval_status"
                        name="approval_status"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                    >
                        @foreach (['all', 'pending', 'approved', 'inactive'] as $status)
                            <option value="{{ $status }}" @selected($approvalStatus === $status)>
                                {{ __('app.admin.therapists.approval_filters.' . $status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="submit"
                    class="rounded-xl bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800"
                >
                    {{ __('app.admin.therapists.search') }}
                </button>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-semibold text-slate-950">
                    {{ __('app.admin.therapists.list_title') }}
                </h2>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse ($therapists as $therapist)
                    <article class="grid gap-4 px-5 py-5 lg:grid-cols-[1.2fr_1fr_1fr_auto] lg:items-center">
                        <div>
                            <p class="text-sm font-semibold text-slate-950">
                                {{ $therapist->user->name }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $therapist->user->email }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-700">
                                {{ $therapist->timezone }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ __('app.admin.therapists.timezone') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700">
                                {{ trans_choice('app.admin.therapists.appointments_count', $therapist->appointments_count, ['count' => $therapist->appointments_count]) }}
                            </span>

                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700">
                                {{ trans_choice('app.admin.therapists.session_types_count', $therapist->session_types_count, ['count' => $therapist->session_types_count]) }}
                            </span>

                            @if (! $therapist->is_active)
                                <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-medium text-rose-800">
                                    {{ __('app.admin.therapists.inactive') }}
                                </span>
                            @elseif ($therapist->is_approved)
                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-800">
                                    {{ __('app.admin.therapists.status_approved') }}
                                </span>
                            @else
                                <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-medium text-amber-800">
                                    {{ __('app.admin.therapists.pending_approval') }}
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2 lg:justify-end">
                            @if ($therapist->is_approved)
                                <form method="POST" action="{{ route('admin.therapists.revoke-approval', $therapist) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="inline-flex rounded-xl border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-50"
                                    >
                                        {{ __('app.admin.therapists.revoke_approval') }}
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.therapists.approve', $therapist) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="inline-flex rounded-xl border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50"
                                    >
                                        {{ __('app.admin.therapists.approve') }}
                                    </button>
                                </form>
                            @endif

                            <a
                                href="{{ route('admin.therapists.edit', $therapist) }}"
                                class="inline-flex rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            >
                                {{ __('app.admin.therapists.edit') }}
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm font-medium text-slate-700">
                            {{ __('app.admin.therapists.empty') }}
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($therapists->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $therapists->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection

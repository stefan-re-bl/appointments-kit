@extends('layouts.admin', [
    'title' => __('app.admin.professionals.title'),
    'header' => __('app.admin.professionals.title'),
])

@section('content')
    <div class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.professionals.index') }}" class="flex flex-col gap-3 lg:flex-row">
                <div class="flex-1">
                    <label for="search" class="sr-only">{{ __('app.admin.professionals.search') }}</label>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="{{ __('app.admin.professionals.search_placeholder') }}"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                    >
                </div>

                <div class="lg:w-64">
                    <label for="approval_status" class="sr-only">{{ __('app.admin.professionals.approval_filter') }}</label>
                    <select
                        id="approval_status"
                        name="approval_status"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                    >
                        @foreach (['all', 'pending', 'approved', 'inactive'] as $status)
                            <option value="{{ $status }}" @selected($approvalStatus === $status)>
                                {{ __('app.admin.professionals.approval_filters.' . $status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="submit"
                    class="rounded-xl bg-brand-title px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-title/90"
                >
                    {{ __('app.admin.professionals.search') }}
                </button>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-semibold text-brand-title">
                    {{ __('app.admin.professionals.list_title') }}
                </h2>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse ($professionals as $professional)
                    <article class="grid gap-4 px-5 py-5 lg:grid-cols-[1.2fr_1fr_1fr_auto] lg:items-center">
                        <div>
                            <p class="text-sm font-semibold text-slate-950">
                                {{ $professional->user->name }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $professional->user->email }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-700">
                                {{ $professional->timezone }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ __('app.admin.professionals.timezone') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700">
                                {{ trans_choice('app.admin.professionals.appointments_count', $professional->appointments_count, ['count' => $professional->appointments_count]) }}
                            </span>

                            @if (! $professional->is_active)
                                <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-medium text-rose-800">
                                    {{ __('app.admin.professionals.inactive') }}
                                </span>
                            @elseif ($professional->is_approved)
                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-800">
                                    {{ __('app.admin.professionals.status_approved') }}
                                </span>
                            @else
                                <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-medium text-amber-800">
                                    {{ __('app.admin.professionals.pending_approval') }}
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2 lg:justify-end">
                            @if ($professional->is_approved)
                                <form method="POST" action="{{ route('admin.professionals.revoke-approval', $professional) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="inline-flex rounded-xl border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-50"
                                    >
                                        {{ __('app.admin.professionals.revoke_approval') }}
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.professionals.approve', $professional) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="inline-flex rounded-xl border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50"
                                    >
                                        {{ __('app.admin.professionals.approve') }}
                                    </button>
                                </form>
                            @endif

                            <a
                                href="{{ route('admin.professionals.edit', $professional) }}"
                                class="inline-flex rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            >
                                {{ __('app.admin.professionals.edit') }}
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm font-medium text-slate-700">
                            {{ __('app.admin.professionals.empty') }}
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($professionals->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $professionals->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection

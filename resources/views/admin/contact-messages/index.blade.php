@extends('layouts.admin', [
    'title' => __('app.admin.contact_messages.title'),
    'header' => __('app.admin.contact_messages.title'),
])

@section('content')
    <div class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="w-full sm:max-w-xs">
                    <label for="status" class="block text-sm font-semibold text-slate-700">
                        {{ __('app.admin.contact_messages.filters.status') }}
                    </label>
                    <select
                        id="status"
                        name="status"
                        class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                    >
                        <option value="all" @selected($selectedStatus === 'all')>
                            {{ __('app.admin.contact_messages.filters.all_statuses') }}
                        </option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>
                                {{ __('app.contact.statuses.' . $status->value) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="inline-flex justify-center rounded-md bg-brand-title px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-title/90">
                    {{ __('app.admin.contact_messages.filters.apply') }}
                </button>

                <a href="{{ route('admin.contact-messages.index') }}" class="inline-flex justify-center rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    {{ __('app.admin.contact_messages.filters.reset') }}
                </a>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('app.admin.contact_messages.list_title') }}</h2>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse ($contactMessages as $contactMessage)
                    <article class="grid gap-5 p-5 lg:grid-cols-[1fr_18rem]">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-base font-semibold text-brand-title">{{ $contactMessage->name }}</h3>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                    {{ __('app.contact.inquiry_types.' . $contactMessage->inquiry_type->value) }}
                                </span>
                                <span class="rounded-full bg-brand-accent-soft px-3 py-1 text-xs font-semibold text-brand-title">
                                    {{ __('app.contact.statuses.' . $contactMessage->status->value) }}
                                </span>
                            </div>

                            <p class="mt-2 text-sm text-slate-600">
                                <a href="mailto:{{ $contactMessage->email }}" class="font-medium text-slate-900 hover:text-brand-title">
                                    {{ $contactMessage->email }}
                                </a>
                                <span class="mx-2 text-slate-300">/</span>
                                {{ $timezoneService->formatForDisplay($contactMessage->created_at, 'd/m/Y H:i') }}
                            </p>

                            <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $contactMessage->message }}</p>
                        </div>

                        <form method="POST" action="{{ route('admin.contact-messages.update', $contactMessage) }}" class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            @csrf
                            @method('PATCH')

                            <label for="status_{{ $contactMessage->id }}" class="block text-sm font-semibold text-slate-700">
                                {{ __('app.admin.contact_messages.status_label') }}
                            </label>
                            <select
                                id="status_{{ $contactMessage->id }}"
                                name="status"
                                class="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-accent focus:ring-brand-accent/30"
                            >
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" @selected($contactMessage->status === $status)>
                                        {{ __('app.contact.statuses.' . $status->value) }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="mt-4 w-full rounded-md bg-brand-title px-4 py-2 text-sm font-semibold text-white hover:bg-brand-title/90">
                                {{ __('app.admin.contact_messages.update_status') }}
                            </button>
                        </form>
                    </article>
                @empty
                    <div class="p-8 text-center text-sm text-slate-600">
                        {{ __('app.admin.contact_messages.empty') }}
                    </div>
                @endforelse
            </div>

            @if ($contactMessages->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $contactMessages->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection

@extends('layouts.guest')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="mb-6">
                <p class="text-sm font-medium text-slate-600">
                    {{ __('appointment_reschedule.badge') }}
                </p>

                <h1 class="mt-2 text-2xl font-bold text-slate-950">
                    {{ __('appointment_reschedule.title') }}
                </h1>

                <p class="mt-2 text-sm text-slate-600">
                    {{ __('appointment_reschedule.intro') }}
                </p>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                    <p class="font-semibold">
                        {{ __('appointment_reschedule.validation_heading') }}
                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-8 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <h2 class="text-base font-semibold text-slate-950">
                    {{ __('appointment_reschedule.current_appointment') }}
                </h2>

                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="font-medium text-slate-500">
                            {{ __('appointment_reschedule.therapist') }}
                        </dt>
                        <dd class="mt-1 text-slate-950">
                            {{ $appointment->therapist->user->name }}
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-slate-500">
                            {{ __('appointment_reschedule.session_type') }}
                        </dt>
                        <dd class="mt-1 text-slate-950">
                            {{ $appointment->sessionType->name }}
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-slate-500">
                            {{ __('appointment_reschedule.current_time') }}
                        </dt>
                        <dd class="mt-1 text-slate-950">
                            {{ $currentStartsAt }} - {{ $currentEndsAt }}
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-slate-500">
                            {{ __('appointment_reschedule.timezone') }}
                        </dt>
                        <dd class="mt-1 text-slate-950">
                            {{ $patientTimezone }}
                        </dd>
                    </div>
                </dl>
            </div>

            @if (! $canReschedule)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    {{ __('appointment_reschedule.policy_blocked') }}
                </div>

                <div class="mt-6">
                    <a
                        href="{{ route('appointments.public.show', ['token' => $appointment->token]) }}"
                        class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        {{ __('appointment_reschedule.back_to_appointment') }}
                    </a>
                </div>
            @else
                <form
                    method="POST"
                    action="{{ $formAction }}"
                    x-data="rescheduleForm({
                        endpoint: @js($slotsEndpoint),
                        therapistId: @js($appointment->therapist_id),
                        sessionTypeId: @js($appointment->session_type_id),
                        durationMinutes: @js((int) $appointment->sessionType->duration_minutes),
                        initialDate: @js(old('date', $initialDate)),
                        initialStartUtc: @js(old('start_utc', '')),
                        patientTimezone: @js($patientTimezone),
                        messages: {
                            loading: @js(__('appointment_reschedule.loading')),
                            empty: @js(__('appointment_reschedule.empty')),
                            fetchError: @js(__('appointment_reschedule.fetch_error')),
                            selected: @js(__('appointment_reschedule.selected_slot')),
                        },
                    })"
                    x-init="init()"
                    class="space-y-6"
                >
                    @csrf

                    <div>
                        <label for="date" class="block text-sm font-medium text-slate-800">
                            {{ __('appointment_reschedule.date_label') }}
                        </label>

                        <input
                            id="date"
                            name="date"
                            type="date"
                            min="{{ $minimumDate }}"
                            x-model="date"
                            x-on:change="loadSlots()"
                            class="mt-2 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500/30"
                            required
                        >

                        @error('date')
                            <p class="mt-2 text-sm text-rose-700">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-4">
                            <label class="block text-sm font-medium text-slate-800">
                                {{ __('appointment_reschedule.slots_label') }}
                            </label>

                            <p class="text-xs text-slate-500">
                                {{ __('appointment_reschedule.slot_note') }}
                            </p>
                        </div>

                        <input type="hidden" name="start_utc" x-bind:value="selectedStartUtc">

                        <div class="mt-3">
                            <p
                                x-show="loading"
                                x-cloak
                                class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600"
                                x-text="messages.loading"
                            ></p>

                            <p
                                x-show="!loading && errorMessage"
                                x-cloak
                                class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"
                                x-text="errorMessage"
                            ></p>

                            <p
                                x-show="!loading && !errorMessage && slots.length === 0"
                                x-cloak
                                class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600"
                                x-text="messages.empty"
                            ></p>

                            <div
                                x-show="!loading && slots.length > 0"
                                x-cloak
                                class="grid gap-3 sm:grid-cols-3"
                            >
                                <template x-for="slot in slots" x-bind:key="slot.start_utc">
                                    <button
                                        type="button"
                                        x-on:click="selectSlot(slot)"
                                        x-bind:aria-pressed="isSelected(slot)"
                                        class="rounded-lg border px-4 py-3 text-center text-sm font-medium transition"
                                        x-bind:class="isSelected(slot)
                                            ? 'border-indigo-600 bg-slate-50 text-slate-600'
                                            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                    >
                                        <span x-text="slot.label"></span>
                                    </button>
                                </template>
                            </div>

                            <p
                                x-show="selectedLabel"
                                x-cloak
                                class="mt-3 text-sm text-slate-600"
                                x-text="messages.selected.replace(':slot', selectedLabel)"
                            ></p>
                        </div>

                        @error('start_utc')
                            <p class="mt-2 text-sm text-rose-700">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-800">
                        {{ __('appointment_reschedule.meet_note') }}
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                        <a
                            href="{{ route('appointments.public.show', ['token' => $appointment->token]) }}"
                            class="inline-flex justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            {{ __('appointment_reschedule.back_to_appointment') }}
                        </a>

                        <button
                            type="submit"
                            x-bind:disabled="!selectedStartUtc || loading"
                            class="inline-flex justify-center rounded-lg bg-indigo-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-800 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ __('appointment_reschedule.submit') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
        function rescheduleForm(config) {
            return {
                date: config.initialDate,
                selectedStartUtc: config.initialStartUtc,
                selectedLabel: '',
                slots: [],
                loading: false,
                errorMessage: '',
                messages: config.messages,

                init() {
                    this.persistPatientTimezone();
                    this.loadSlots();
                },

                persistPatientTimezone() {
                    if (!config.patientTimezone) {
                        return;
                    }

                    document.cookie = `user_timezone=${config.patientTimezone}; path=/; SameSite=Lax`;
                },

                async loadSlots() {
                    this.errorMessage = '';
                    this.slots = [];
                    this.selectedStartUtc = '';
                    this.selectedLabel = '';

                    if (!this.date) {
                        return;
                    }

                    this.loading = true;

                    try {
                        const url = new URL(config.endpoint, window.location.origin);

                        url.searchParams.set('therapist_id', config.therapistId);
                        url.searchParams.set('session_type_id', config.sessionTypeId);
                        url.searchParams.set('duration', config.durationMinutes);
                        url.searchParams.set('duration_minutes', config.durationMinutes);
                        url.searchParams.set('date', this.date);
                        url.searchParams.set('timezone', config.patientTimezone);
                        const response = await fetch(url.toString(), {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Unable to load slots.');
                        }

                        const payload = await response.json();

                        this.slots = Array.isArray(payload)
                            ? payload
                            : (payload.slots ?? []);
                    } catch (error) {
                        this.errorMessage = this.messages.fetchError;
                    } finally {
                        this.loading = false;
                    }
                },

                selectSlot(slot) {
                    this.selectedStartUtc = slot.start_utc;
                    this.selectedLabel = slot.label;
                },

                isSelected(slot) {
                    return this.selectedStartUtc === slot.start_utc;
                },
            };
        }
    </script>
@endsection

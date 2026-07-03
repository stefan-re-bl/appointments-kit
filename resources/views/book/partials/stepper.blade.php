@php
    $steps = [
        __('app.select_therapist'),
        __('app.select_session_type'),
        __('app.select_date'),
        __('app.select_time'),
        __('app.confirm_booking'),
        __('app.booking_confirmed'),
    ];
    $currentStep = $currentStep ?? 1;
@endphp

<nav aria-label="{{ __('app.booking_title') }}" class="mb-8">
    <ol class="grid grid-cols-5 gap-2">
        @foreach ($steps as $index => $label)
            @php
                $stepNumber = $index + 1;
                $isActive = $stepNumber === $currentStep;
                $isComplete = $stepNumber < $currentStep;
            @endphp

            <li class="min-w-0">
                <div class="flex flex-col items-center gap-2">
                    <span @class([
                        'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold ring-1',
                        'bg-indigo-700 text-white ring-indigo-700' => $isActive || $isComplete,
                        'bg-white text-slate-500 ring-slate-200' => ! $isActive && ! $isComplete,
                    ])>
                        {{ $stepNumber }}
                    </span>
                    <span @class([
                        'hidden max-w-full truncate text-center text-xs font-medium sm:block',
                        'text-indigo-700' => $isActive,
                        'text-slate-500' => ! $isActive,
                    ])>
                        {{ $label }}
                    </span>
                </div>
            </li>
        @endforeach
    </ol>
</nav>

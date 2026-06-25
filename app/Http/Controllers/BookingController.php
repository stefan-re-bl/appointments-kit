<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SendBookingConfirmedEmails;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Rules\ValidTimezone;
use App\Services\BookingService;
use App\Services\SlotGenerationService;
use App\Services\TimezoneService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

final class BookingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [];
    }

    /**
     * Paso 1: Lista de Terapeutas
     */
    public function index(): View
    {
        session()->forget('booking');

        $therapists = Therapist::with('user')
            ->where('is_active', true)
            ->whereHas('user', fn (Builder $query) => $query->where('role', 'therapist'))
            ->get();

        return view('book.index', compact('therapists'));
    }

    /**
     * Procesar Paso 1: Guardar Terapeuta
     */
    public function storeTherapist(Request $request): RedirectResponse
    {
        $request->validate([
            'therapist_id' => [
                'required',
                'integer',
                Rule::exists('therapists', 'id')->where('is_active', true),
            ],
        ]);

        $therapist = Therapist::query()
            ->where('is_active', true)
            ->whereHas('user', fn (Builder $query) => $query->where('role', 'therapist'))
            ->find((int) $request->therapist_id);

        if (! $therapist) {
            throw ValidationException::withMessages([
                'therapist_id' => __('app.error_booking_slot'),
            ]);
        }

        session()->put('booking.therapist_id', $therapist->id);

        return Redirect::route('book.session');
    }

    /**
     * Paso 2: Tipos de Sesión
     */
    public function session(): View|RedirectResponse
    {
        $therapist = $this->activeTherapistFromSession();

        if (! $therapist) {
            return Redirect::route('book.index');
        }

        $sessions = SessionType::where('therapist_id', $therapist->id)
            ->where('is_active', true)
            ->get();

        return view('book.session', compact('sessions'));
    }

    /**
     * Procesar Paso 2: Guardar Sesión
     */
    public function storeSession(Request $request): RedirectResponse
    {
        $therapist = $this->activeTherapistFromSession();

        if (! $therapist) {
            return Redirect::route('book.index');
        }

        $request->validate([
            'session_type_id' => ['required', 'integer', 'exists:session_types,id'],
        ]);

        $sessionType = SessionType::where('therapist_id', $therapist->id)
            ->where('is_active', true)
            ->findOrFail((int) $request->session_type_id);

        session()->put('booking.session_type_id', $sessionType->id);

        return Redirect::route('book.date');
    }

    /**
     * Paso 3: Selección de Fecha
     */
    public function date(): View|RedirectResponse
    {
        $therapist = $this->activeTherapistFromSession();

        if (! $therapist || ! session('booking.session_type_id')) {
            return Redirect::route('book.index');
        }

        $sessionTypeExists = SessionType::where('therapist_id', $therapist->id)
            ->where('is_active', true)
            ->whereKey((int) session('booking.session_type_id'))
            ->exists();

        if (! $sessionTypeExists) {
            session()->forget(['booking.session_type_id', 'booking.date', 'booking.starts_at_utc']);

            return Redirect::route('book.session');
        }

        $minDate = now(app('user.timezone'))->toDateString();

        return view('book.date', compact('minDate'));
    }

    /**
     * Procesar Paso 3: Guardar Fecha
     */
    public function storeDate(Request $request): RedirectResponse
    {
        $therapist = $this->activeTherapistFromSession();

        if (! $therapist || ! session('booking.session_type_id')) {
            return Redirect::route('book.index');
        }

        $sessionTypeExists = SessionType::where('therapist_id', $therapist->id)
            ->where('is_active', true)
            ->whereKey((int) session('booking.session_type_id'))
            ->exists();

        if (! $sessionTypeExists) {
            session()->forget(['booking.session_type_id', 'booking.date', 'booking.starts_at_utc']);

            return Redirect::route('book.session');
        }

        $minDate = now(app('user.timezone'))->toDateString();

        $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$minDate],
        ]);

        session()->put('booking.date', $request->date);

        return Redirect::route('book.time');
    }

    /**
     * Paso 4: Selección de Hora (Alpine + API)
     */
    public function time(): View|RedirectResponse
    {
        $therapist = $this->activeTherapistFromSession();

        if (! $therapist || ! session('booking.session_type_id') || ! session('booking.date')) {
            return Redirect::route('book.index');
        }

        $sessionType = SessionType::where('therapist_id', $therapist->id)
            ->where('is_active', true)
            ->findOrFail((int) session('booking.session_type_id'));

        $date = session('booking.date');

        return view('book.time', [
            'sessionType' => $sessionType,
            'date' => $date,
            'therapistId' => $therapist->id,
        ]);
    }

    /**
     * Procesar Paso 4: Guardar Hora (UTC)
     */
    public function storeTime(Request $request): RedirectResponse
    {
        $therapist = $this->activeTherapistFromSession();

        if (! $therapist || ! session('booking.session_type_id') || ! session('booking.date')) {
            return Redirect::route('book.index');
        }

        $sessionTypeExists = SessionType::where('therapist_id', $therapist->id)
            ->where('is_active', true)
            ->whereKey((int) session('booking.session_type_id'))
            ->exists();

        if (! $sessionTypeExists) {
            session()->forget(['booking.session_type_id', 'booking.date', 'booking.starts_at_utc']);

            return Redirect::route('book.session');
        }

        $request->validate([
            'starts_at_utc' => ['required', 'date'],
        ]);

        session()->put('booking.starts_at_utc', $request->starts_at_utc);

        return Redirect::route('book.confirm');
    }

    /**
     * Paso 5: Confirmación y Datos del Paciente
     */
    public function confirm(TimezoneService $timezoneService): View|RedirectResponse
    {
        $therapist = $this->activeTherapistFromSession();
        $sessionTypeId = session('booking.session_type_id');
        $dateLocal = session('booking.date');
        $startsAtUtc = session('booking.starts_at_utc');

        if (! $therapist || ! $sessionTypeId || ! $dateLocal || ! $startsAtUtc) {
            return Redirect::route('book.index');
        }

        $therapist->load('user');

        $sessionType = SessionType::where('therapist_id', $therapist->id)
            ->where('is_active', true)
            ->findOrFail((int) $sessionTypeId);

        $localTime = $timezoneService->formatForDisplay($startsAtUtc, 'H:i');

        return view('book.confirm', compact('therapist', 'sessionType', 'dateLocal', 'localTime'));
    }

    /**
     * Paso Final: Crear Cita
     */
    public function store(
        Request $request,
        BookingService $bookingService,
        SlotGenerationService $slotGenerationService,
    ): RedirectResponse {
        $therapist = $this->activeTherapistFromSession();

        if (
            ! $therapist ||
            ! session('booking.session_type_id') ||
            ! session('booking.date') ||
            ! session('booking.starts_at_utc')
        ) {
            return Redirect::route('book.index');
        }

        $inputTimezone = $request->input('patient_timezone', 'UTC');

        $request->merge([
            'patient_timezone' => ValidTimezone::normalize($inputTimezone) ?? 'UTC',
        ]);

        $validated = $request->validate([
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_email' => ['required', 'email', 'max:255'],
            'patient_timezone' => ['required', 'string', new ValidTimezone],
        ]);

        $sessionType = SessionType::where('therapist_id', $therapist->id)
            ->where('is_active', true)
            ->findOrFail((int) session('booking.session_type_id'));

        $selectedSlot = $this->findSelectedSlot(
            $slotGenerationService,
            $therapist,
            (string) session('booking.date'),
            (int) $sessionType->duration_minutes,
            (string) session('booking.starts_at_utc'),
        );

        if ($selectedSlot === null) {
            throw ValidationException::withMessages([
                'general' => __('app.error_booking_slot'),
            ]);
        }

        $startUtc = Carbon::parse((string) $selectedSlot['start_utc'], 'UTC')->utc();
        $endUtc = Carbon::parse((string) $selectedSlot['end_utc'], 'UTC')->utc();

        $data = [
            'therapist_id' => $therapist->id,
            'session_type_id' => $sessionType->id,
            'patient_name' => $validated['patient_name'],
            'patient_email' => $validated['patient_email'],
            'patient_timezone' => $validated['patient_timezone'],
            'price' => $sessionType->price,
            'currency' => $sessionType->currency,
            'starts_at' => $startUtc->toDateTimeString(),
            'ends_at' => $endUtc->toDateTimeString(),
        ];

        try {
            $appointment = $bookingService->bookSlot($data);

            if (! $appointment) {
                throw ValidationException::withMessages([
                    'general' => __('app.error_booking_slot'),
                ]);
            }

            $appointment->loadMissing(['therapist.user', 'sessionType']);

            SendBookingConfirmedEmails::dispatch($appointment->id);

            session()->put('booking.appointment_token', $appointment->token);

            session()->forget([
                'booking.therapist_id',
                'booking.session_type_id',
                'booking.date',
                'booking.starts_at_utc',
            ]);

            return Redirect::route('book.success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Booking error', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            throw ValidationException::withMessages([
                'general' => __('app.error_booking_slot'),
            ]);
        }
    }

    public function success(): View
    {
        return view('book.success');
    }

    /**
     * API para obtener slots (Usada por Alpine.js)
     */
    public function getSlotsApi(Request $request, SlotGenerationService $slotGenerationService): JsonResponse
    {
        $this->applyRequestedTimezone($request);

        $request->validate([
            'therapist_id' => [
                'required',
                'integer',
                Rule::exists('therapists', 'id')->where('is_active', true),
            ],
            'date' => ['required', 'date_format:Y-m-d'],
            'duration' => ['required', 'integer'],
            'timezone' => ['nullable', 'string'],
        ]);

        $therapist = Therapist::query()
            ->where('is_active', true)
            ->whereHas('user', fn (Builder $query) => $query->where('role', 'therapist'))
            ->findOrFail((int) $request->therapist_id);

        $slots = $slotGenerationService->generate(
            $therapist,
            $request->date,
            (int) $request->duration,
        );

        return response()->json($slots);
    }

    private function activeTherapistFromSession(): ?Therapist
    {
        $therapistId = session('booking.therapist_id');

        if (! $therapistId) {
            return null;
        }

        return Therapist::query()
            ->with('user')
            ->where('is_active', true)
            ->whereHas('user', fn (Builder $query) => $query->where('role', 'therapist'))
            ->find((int) $therapistId);
    }

    private function applyRequestedTimezone(Request $request): void
    {
        $timezone = ValidTimezone::normalize($request->query('timezone'));

        if ($timezone === null) {
            return;
        }

        app()->instance('user.timezone', $timezone);
    }

    /**
     * @return array{start_utc: mixed, end_utc: mixed, label?: mixed}|null
     */
    private function findSelectedSlot(
        SlotGenerationService $slotGenerationService,
        Therapist $therapist,
        string $date,
        int $durationMinutes,
        string $selectedStartUtc,
    ): ?array {
        $slots = $slotGenerationService->generate(
            $therapist,
            $date,
            $durationMinutes,
        );

        foreach ($slots as $slot) {
            if (
                ! is_array($slot)
                || ! array_key_exists('start_utc', $slot)
                || ! array_key_exists('end_utc', $slot)
            ) {
                continue;
            }

            try {
                $slotStartUtc = Carbon::parse((string) $slot['start_utc'], 'UTC')->utc();
                $requestedStartUtc = Carbon::parse($selectedStartUtc, 'UTC')->utc();
            } catch (Throwable) {
                return null;
            }

            if ($slotStartUtc->equalTo($requestedStartUtc)) {
                return $slot;
            }
        }

        return null;
    }
}

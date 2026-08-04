<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SupportedLocale;
use App\Jobs\SendBookingConfirmedEmails;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Rules\ValidTimezone;
use App\Services\AvailableSlotResolver;
use App\Services\BookingService;
use App\Services\CountryTimezoneService;
use App\Services\Notifications\PhoneNumberNormalizer;
use App\Services\Notifications\WhatsAppDeliveryDispatcher;
use App\Services\SlotGenerationService;
use App\Services\TimezoneService;
use Carbon\Carbon;
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
            ->publiclyBookable()
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
                Rule::exists('therapists', 'id')
                    ->where('is_active', true)
                    ->where('is_approved', true),
            ],
        ]);

        $therapist = Therapist::query()
            ->publiclyBookable()
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
    public function date(Request $request, CountryTimezoneService $countryTimezoneService): View|RedirectResponse
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

        $countries = $countryTimezoneService->countries();
        $countryTimezones = $countryTimezoneService->countryTimezones();
        $timezoneCountries = $countryTimezoneService->timezoneCountries();
        $sessionTimezone = ValidTimezone::normalize(session('booking.patient_timezone'));
        $cookieTimezone = ValidTimezone::normalize($request->cookie('user_timezone'));
        $sessionCountry = session('booking.patient_country');
        $patientCountry = $countryTimezoneService->isSupportedCountry($sessionCountry)
            ? strtoupper((string) $sessionCountry)
            : $countryTimezoneService->countryForTimezone($sessionTimezone ?? $cookieTimezone);
        $patientCountry ??= 'AR';
        $defaultTimezone = ValidTimezone::normalize($countryTimezoneService->timezoneForCountry($patientCountry)) ?? 'UTC';
        $minDate = now($sessionTimezone ?? $cookieTimezone ?? $defaultTimezone)->toDateString();
        $selectedDate = (string) ($request->old('date') ?: session('booking.date', $minDate));
        $timezoneOptions = $countryTimezoneService->timezoneOptionsForCountryOnDate($patientCountry, $selectedDate);
        $patientTimezone = $sessionTimezone ?? $cookieTimezone;

        if ($patientTimezone === null || ! array_key_exists($patientTimezone, $timezoneOptions)) {
            $patientTimezone = array_key_first($timezoneOptions) ?? $defaultTimezone;
        }

        $timezoneWasConfirmed = $countryTimezoneService->isSupportedCountry($sessionCountry);
        $minDate = now($patientTimezone)->toDateString();

        return view('book.date', compact(
            'countries',
            'countryTimezones',
            'minDate',
            'patientCountry',
            'patientTimezone',
            'selectedDate',
            'timezoneCountries',
            'timezoneOptions',
            'timezoneWasConfirmed',
        ));
    }

    /**
     * Procesar Paso 3: Guardar Fecha
     */
    public function storeDate(Request $request, CountryTimezoneService $countryTimezoneService): RedirectResponse
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

        $countries = $countryTimezoneService->countries();
        $patientCountry = strtoupper((string) $request->input('patient_country'));
        $selectedDate = (string) $request->input('date');
        $selectedTimezone = is_string($request->input('patient_timezone'))
            ? trim((string) $request->input('patient_timezone'))
            : null;
        $selectedTimezone = $selectedTimezone === '' ? null : $selectedTimezone;
        $timezoneOptions = $countryTimezoneService->timezoneOptionsForCountryOnDate($patientCountry, $selectedDate);
        $normalizedTimezone = ValidTimezone::normalize(
            $countryTimezoneService->timezoneForLocation($patientCountry, $selectedDate, $selectedTimezone)
        );

        $request->merge([
            'patient_country' => $patientCountry,
            'patient_timezone' => $selectedTimezone,
        ]);

        $minDate = now($normalizedTimezone ?? $countryTimezoneService->timezoneForCountry($patientCountry) ?? 'UTC')->toDateString();

        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$minDate],
            'patient_country' => ['required', 'string', Rule::in(array_keys($countries))],
            'patient_timezone' => [
                Rule::requiredIf(fn (): bool => $countryTimezoneService->regionIsRequired($patientCountry, $selectedDate)),
                'nullable',
                'string',
                Rule::in(array_keys($timezoneOptions)),
            ],
        ]);

        session()->put([
            'booking.date' => $validated['date'],
            'booking.patient_country' => $validated['patient_country'],
            'booking.patient_timezone' => $normalizedTimezone,
        ]);
        session()->forget('booking.starts_at_utc');

        return Redirect::route('book.time');
    }

    /**
     * Paso 4: Selección de Hora (Alpine + API)
     */
    public function time(CountryTimezoneService $countryTimezoneService): View|RedirectResponse
    {
        $therapist = $this->activeTherapistFromSession();

        $patientTimezone = ValidTimezone::normalize(session('booking.patient_timezone'));

        if (
            ! $therapist ||
            ! session('booking.session_type_id') ||
            ! session('booking.date') ||
            $patientTimezone === null
        ) {
            return Redirect::route('book.index');
        }

        $sessionType = SessionType::where('therapist_id', $therapist->id)
            ->where('is_active', true)
            ->findOrFail((int) session('booking.session_type_id'));

        $date = session('booking.date');
        $patientCountry = $countryTimezoneService->isSupportedCountry(session('booking.patient_country'))
            ? (string) session('booking.patient_country')
            : $countryTimezoneService->countryForTimezone($patientTimezone);
        $timezoneOptions = $countryTimezoneService->timezoneOptionsForCountryOnDate($patientCountry, $date);
        $patientTimezoneLabel = $timezoneOptions[$patientTimezone]['label'] ?? null;

        return view('book.time', [
            'sessionType' => $sessionType,
            'date' => $date,
            'therapistId' => $therapist->id,
            'patientCountry' => $patientCountry,
            'patientTimezoneLabel' => count($timezoneOptions) > 1 ? $patientTimezoneLabel : null,
            'patientTimezone' => $patientTimezone,
        ]);
    }

    /**
     * Procesar Paso 4: Guardar Hora (UTC)
     */
    public function storeTime(Request $request): RedirectResponse
    {
        $therapist = $this->activeTherapistFromSession();

        if (
            ! $therapist ||
            ! session('booking.session_type_id') ||
            ! session('booking.date') ||
            ! ValidTimezone::isValid(session('booking.patient_timezone'))
        ) {
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
    public function confirm(
        TimezoneService $timezoneService,
        CountryTimezoneService $countryTimezoneService,
    ): View|RedirectResponse {
        $therapist = $this->activeTherapistFromSession();
        $sessionTypeId = session('booking.session_type_id');
        $dateLocal = session('booking.date');
        $startsAtUtc = session('booking.starts_at_utc');
        $patientTimezone = ValidTimezone::normalize(session('booking.patient_timezone'));

        if (! $therapist || ! $sessionTypeId || ! $dateLocal || ! $startsAtUtc || $patientTimezone === null) {
            return Redirect::route('book.index');
        }

        $therapist->load('user');

        $sessionType = SessionType::where('therapist_id', $therapist->id)
            ->where('is_active', true)
            ->findOrFail((int) $sessionTypeId);

        $localTime = $timezoneService->formatForDisplay($startsAtUtc, 'H:i', $patientTimezone);
        $patientCountry = $countryTimezoneService->isSupportedCountry(session('booking.patient_country'))
            ? (string) session('booking.patient_country')
            : $countryTimezoneService->countryForTimezone($patientTimezone);
        $timezoneOptions = $countryTimezoneService->timezoneOptionsForCountryOnDate($patientCountry, $dateLocal);
        $patientTimezoneLabel = $timezoneOptions[$patientTimezone]['label'] ?? null;
        $patientTimezoneLabel = count($timezoneOptions) > 1 ? $patientTimezoneLabel : null;

        return view('book.confirm', compact(
            'therapist',
            'sessionType',
            'dateLocal',
            'localTime',
            'patientCountry',
            'patientTimezoneLabel',
            'patientTimezone',
        ));
    }

    /**
     * Paso Final: Crear Cita
     */
    public function store(
        Request $request,
        BookingService $bookingService,
        AvailableSlotResolver $availableSlotResolver,
        PhoneNumberNormalizer $phoneNumberNormalizer,
        WhatsAppDeliveryDispatcher $whatsAppDeliveryDispatcher,
    ): RedirectResponse {
        $therapist = $this->activeTherapistFromSession();

        if (
            ! $therapist ||
            ! session('booking.session_type_id') ||
            ! session('booking.date') ||
            ! session('booking.starts_at_utc') ||
            ! ValidTimezone::isValid(session('booking.patient_timezone'))
        ) {
            return Redirect::route('book.index');
        }

        $validated = $request->validate([
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_email' => ['required', 'email', 'max:255'],
            'patient_phone' => ['nullable', 'string', 'max:32'],
            'accepted_whatsapp_communications' => ['sometimes', 'accepted'],
            'accepted_terms' => ['accepted'],
            'accepted_email_communications' => ['accepted'],
        ]);

        $patientTimezone = ValidTimezone::normalize(session('booking.patient_timezone'));
        $patientPhone = $phoneNumberNormalizer->normalize($validated['patient_phone'] ?? null);

        if ($patientTimezone === null) {
            return Redirect::route('book.index');
        }

        if (filled($validated['patient_phone'] ?? null) && $patientPhone === null) {
            throw ValidationException::withMessages([
                'patient_phone' => __('app.whatsapp.validation_phone'),
            ]);
        }

        if ($request->boolean('accepted_whatsapp_communications') && $patientPhone === null) {
            throw ValidationException::withMessages([
                'patient_phone' => __('app.whatsapp.validation_phone'),
            ]);
        }

        $sessionType = SessionType::where('therapist_id', $therapist->id)
            ->where('is_active', true)
            ->findOrFail((int) session('booking.session_type_id'));

        $selectedSlot = $availableSlotResolver->resolve(
            $therapist,
            (string) session('booking.date'),
            (int) $sessionType->duration_minutes,
            (string) session('booking.starts_at_utc'),
            $patientTimezone,
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
            'patient_phone' => $patientPhone,
            'patient_timezone' => $patientTimezone,
            'patient_locale' => SupportedLocale::normalize(app()->getLocale()),
            'terms_accepted_at' => now('UTC'),
            'patient_whatsapp_opt_in_at' => $request->boolean('accepted_whatsapp_communications') ? now('UTC') : null,
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
            $whatsAppDeliveryDispatcher->dispatchBookingConfirmed($appointment);

            session()->put('booking.appointment_token', $appointment->token);

            session()->forget([
                'booking.therapist_id',
                'booking.session_type_id',
                'booking.date',
                'booking.starts_at_utc',
                'booking.patient_country',
                'booking.patient_timezone',
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
        $validated = $request->validate([
            'therapist_id' => [
                'required',
                'integer',
                Rule::exists('therapists', 'id')
                    ->where('is_active', true)
                    ->where('is_approved', true),
            ],
            'date' => ['required', 'date_format:Y-m-d'],
            'duration' => ['required', 'integer'],
            'timezone' => ['required', 'string', new ValidTimezone],
        ]);

        $timezone = ValidTimezone::normalize($validated['timezone']);

        if ($timezone === null) {
            throw ValidationException::withMessages([
                'timezone' => __('booking_timezone.invalid'),
            ]);
        }

        $therapist = Therapist::query()
            ->publiclyBookable()
            ->findOrFail((int) $request->therapist_id);

        $slots = $slotGenerationService->generate(
            $therapist,
            $validated['date'],
            (int) $validated['duration'],
            $timezone,
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
            ->publiclyBookable()
            ->find((int) $therapistId);
    }
}

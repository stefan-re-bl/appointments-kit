<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SupportedLocale;
use App\Jobs\SendBookingConfirmedEmails;
use App\Models\Professional;
use App\Models\SessionType;
use App\Models\User;
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

    public function index(Request $request): RedirectResponse
    {
        session()->forget('booking');

        $context = $this->bookingContext($request);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return Redirect::route('book.date');
    }

    public function storeProfessional(Request $request): RedirectResponse
    {
        return $this->index($request);
    }

    public function session(Request $request): RedirectResponse
    {
        $context = $this->bookingContext($request);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return Redirect::route('book.date');
    }

    public function storeSession(Request $request): RedirectResponse
    {
        $context = $this->bookingContext($request);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return Redirect::route('book.date');
    }

    /**
     * Paso 3: Selección de Fecha
     */
    public function date(Request $request, CountryTimezoneService $countryTimezoneService): View|RedirectResponse
    {
        $context = $this->bookingContext($request);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $countries = $countryTimezoneService->countries();
        $countryTimezones = $countryTimezoneService->countryTimezones();
        $sessionTimezone = ValidTimezone::normalize(session('booking.patient_timezone'));
        $sessionCountry = session('booking.patient_country');
        $patientCountry = $countryTimezoneService->isSupportedCountry($sessionCountry)
            ? strtoupper((string) $sessionCountry)
            : $countryTimezoneService->countryForTimezone($sessionTimezone);
        $defaultTimezone = $patientCountry
            ? ValidTimezone::normalize($countryTimezoneService->timezoneForCountry($patientCountry)) ?? 'UTC'
            : 'UTC';
        $minDate = now($sessionTimezone ?? $defaultTimezone)->toDateString();
        $selectedDate = (string) ($request->old('date') ?: session('booking.date', $minDate));
        $timezoneOptions = $patientCountry
            ? $countryTimezoneService->timezoneOptionsForCountryOnDate($patientCountry, $selectedDate)
            : [];
        $patientTimezone = $sessionTimezone;

        if ($patientTimezone !== null && ! array_key_exists($patientTimezone, $timezoneOptions)) {
            $patientTimezone = array_key_first($timezoneOptions) ?? $defaultTimezone;
        }

        $minDate = now($patientTimezone ?? $defaultTimezone)->toDateString();

        return view('book.date', compact(
            'countries',
            'countryTimezones',
            'minDate',
            'patientCountry',
            'patientTimezone',
            'selectedDate',
            'timezoneOptions',
        ));
    }

    /**
     * Procesar Paso 3: Guardar Fecha
     */
    public function storeDate(Request $request, CountryTimezoneService $countryTimezoneService): RedirectResponse
    {
        $context = $this->bookingContext($request);

        if ($context instanceof RedirectResponse) {
            return $context;
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
    public function time(Request $request, CountryTimezoneService $countryTimezoneService): View|RedirectResponse
    {
        $context = $this->bookingContext($request);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        /** @var Professional $professional */
        $professional = $context['professional'];
        $patientTimezone = ValidTimezone::normalize(session('booking.patient_timezone'));

        if (
            ! session('booking.date') ||
            $patientTimezone === null
        ) {
            return Redirect::route('book.index');
        }

        $date = session('booking.date');
        $patientCountry = $countryTimezoneService->isSupportedCountry(session('booking.patient_country'))
            ? (string) session('booking.patient_country')
            : $countryTimezoneService->countryForTimezone($patientTimezone);
        $timezoneOptions = $countryTimezoneService->timezoneOptionsForCountryOnDate($patientCountry, $date);
        $patientTimezoneLabel = $timezoneOptions[$patientTimezone]['label'] ?? null;

        return view('book.time', [
            'date' => $date,
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
        $context = $this->bookingContext($request);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        if (
            ! session('booking.date') ||
            ! ValidTimezone::isValid(session('booking.patient_timezone'))
        ) {
            return Redirect::route('book.index');
        }

        $request->validate([
            'starts_at_utc' => ['required', 'date'],
        ]);

        session()->put('booking.starts_at_utc', $request->starts_at_utc);

        return Redirect::route('book.confirm');
    }

    /**
     * Paso 5: Confirmación y Datos del Cliente
     */
    public function confirm(
        Request $request,
        TimezoneService $timezoneService,
        CountryTimezoneService $countryTimezoneService,
    ): View|RedirectResponse {
        $context = $this->bookingContext($request);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        /** @var Professional $professional */
        $professional = $context['professional'];
        $dateLocal = session('booking.date');
        $startsAtUtc = session('booking.starts_at_utc');
        $patientTimezone = ValidTimezone::normalize(session('booking.patient_timezone'));

        if (! $dateLocal || ! $startsAtUtc || $patientTimezone === null) {
            return Redirect::route('book.index');
        }

        $professional->load('user');

        $localTime = $timezoneService->formatForDisplay($startsAtUtc, 'H:i', $patientTimezone);
        $patientCountry = $countryTimezoneService->isSupportedCountry(session('booking.patient_country'))
            ? (string) session('booking.patient_country')
            : $countryTimezoneService->countryForTimezone($patientTimezone);
        $timezoneOptions = $countryTimezoneService->timezoneOptionsForCountryOnDate($patientCountry, $dateLocal);
        $patientTimezoneLabel = $timezoneOptions[$patientTimezone]['label'] ?? null;
        $patientTimezoneLabel = count($timezoneOptions) > 1 ? $patientTimezoneLabel : null;

        return view('book.confirm', compact(
            'professional',
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
        $context = $this->bookingContext($request);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        /** @var Professional $professional */
        $professional = $context['professional'];
        /** @var SessionType $sessionType */
        $sessionType = $context['sessionType'];

        if (
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

        $selectedSlot = $availableSlotResolver->resolve(
            $professional,
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
            'professional_id' => $professional->id,
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

            $appointment->loadMissing(['professional.user', 'sessionType']);

            SendBookingConfirmedEmails::dispatch($appointment->id);
            $whatsAppDeliveryDispatcher->dispatchBookingConfirmed($appointment);

            session()->put('booking.appointment_token', $appointment->token);

            session()->forget([
                'booking.professional_id',
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

    public function success(Request $request): View|RedirectResponse
    {
        $context = $this->bookingContext($request);

        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return view('book.success');
    }

    /**
     * API para obtener slots (Usada por Alpine.js)
     */
    public function getSlotsApi(Request $request, SlotGenerationService $slotGenerationService): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'timezone' => ['required', 'string', new ValidTimezone],
        ]);

        $context = $this->bookingContext($request);

        if ($context instanceof RedirectResponse) {
            abort(403);
        }

        /** @var Professional $professional */
        $professional = $context['professional'];
        /** @var SessionType $sessionType */
        $sessionType = $context['sessionType'];

        $timezone = ValidTimezone::normalize($validated['timezone']);

        if ($timezone === null) {
            throw ValidationException::withMessages([
                'timezone' => __('booking_timezone.invalid'),
            ]);
        }

        $slots = $slotGenerationService->generate(
            $professional,
            $validated['date'],
            (int) $sessionType->duration_minutes,
            $timezone,
        );

        return response()->json($slots);
    }

    /**
     * @return array{professional: Professional, sessionType: SessionType}|RedirectResponse
     */
    private function bookingContext(Request $request): array|RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return Redirect::route('home');
        }

        if ($user->professional === null) {
            abort(403);
        }

        $professional = Professional::query()
            ->with('user')
            ->publiclyBookable()
            ->whereKey($user->professional->id)
            ->first();

        if (! $professional) {
            return Redirect::route('dashboard')
                ->with('warning', __('app.booking.unavailable_professional'));
        }

        $sessionType = $this->defaultSessionType($professional);

        if (! $sessionType) {
            return Redirect::route('dashboard')
                ->with('warning', __('app.booking.missing_internal_price'));
        }

        session()->put([
            'booking.professional_id' => $professional->id,
            'booking.session_type_id' => $sessionType->id,
        ]);

        return [
            'professional' => $professional,
            'sessionType' => $sessionType,
        ];
    }

    private function defaultSessionType(Professional $professional): ?SessionType
    {
        return SessionType::query()
            ->where('professional_id', $professional->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }
}

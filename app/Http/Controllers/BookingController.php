<?php

namespace App\Http\Controllers;

use App\Events\AppointmentBooked;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Services\BookingService;
use App\Services\TimezoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // <--- ESTE ES EL IMPORT FALTANTE
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function __construct(
        private TimezoneService $timezoneService,
        private BookingService $bookingService
    ) {}

    /**
     * Paso 1: Lista de Terapeutas
     */
    public function index()
    {
        // Limpiamos sesión previa por seguridad
        session()->forget(['booking.therapist_id', 'booking.session_type_id', 'booking.date', 'booking.starts_at_utc']);

        $therapists = Therapist::with('user')
            ->where('is_active', true)
            ->whereHas('user', fn($q) => $q->where('role', 'therapist'))
            ->get();

        return view('book.index', compact('therapists'));
    }

    /**
     * Procesar Paso 1: Guardar Terapeuta
     */
    public function storeTherapist(Request $request)
    {
        $request->validate(['therapist_id' => 'required|exists:therapists,id']);
        session()->put('booking.therapist_id', $request->therapist_id);
        return Redirect::route('book.session');
    }

    /**
     * Paso 2: Tipos de Sesión
     */
    public function session()
    {
        $therapistId = session('booking.therapist_id');
        if (!$therapistId) return Redirect::route('book');

        $sessions = SessionType::where('therapist_id', $therapistId)
            ->where('is_active', true)
            ->get();

        return view('book.session', compact('sessions'));
    }

    /**
     * Procesar Paso 2: Guardar Sesión
     */
    public function storeSession(Request $request)
    {
        $request->validate(['session_type_id' => 'required|exists:session_types,id']);
        session()->put('booking.session_type_id', $request->session_type_id);
        return Redirect::route('book.date');
    }

    /**
     * Paso 3: Selección de Fecha
     */
    public function date()
    {
        if (!session('booking.therapist_id') || !session('booking.session_type_id')) {
            return Redirect::route('book');
        }
        
        // Min date = hoy en zona local del usuario
        $minDate = now(app('user.timezone'))->toDateString();

        return view('book.date', compact('minDate'));
    }

    /**
     * Procesar Paso 3: Guardar Fecha
     */
    public function storeDate(Request $request)
    {
        $request->validate(['date' => 'required|date|after_or_equal:today']);
        session()->put('booking.date', $request->date);
        return Redirect::route('book.time');
    }

    /**
     * Paso 4: Selección de Hora (Alpine + API)
     */
    public function time()
    {
        if (!session('booking.therapist_id') || !session('booking.session_type_id') || !session('booking.date')) {
            return Redirect::route('book');
        }

        $sessionType = SessionType::find(session('booking.session_type_id'));
        $date = session('booking.date'); // YYYY-MM-DD

        return view('book.time', [
            'sessionType' => $sessionType,
            'date' => $date,
            'therapistId' => session('booking.therapist_id')
        ]);
    }

    /**
     * Procesar Paso 4: Guardar Hora (UTC)
     */
    public function storeTime(Request $request)
    {
        $request->validate(['starts_at_utc' => 'required|date']);
        session()->put('booking.starts_at_utc', $request->starts_at_utc);
        return Redirect::route('book.confirm');
    }

    /**
     * Paso 5: Confirmación y Datos del Paciente
     */
    public function confirm()
    {
        $therapistId = session('booking.therapist_id');
        $sessionTypeId = session('booking.session_type_id');
        $dateLocal = session('booking.date');
        $startsAtUtc = session('booking.starts_at_utc');

        if (!$therapistId || !$sessionTypeId || !$dateLocal || !$startsAtUtc) {
            return Redirect::route('book');
        }

        $therapist = Therapist::with('user')->find($therapistId);
        $sessionType = SessionType::find($sessionTypeId);
        
        // Calcular hora local para display
        $userTz = app('user.timezone');
        $localTime = $this->timezoneService->formatForDisplay($startsAtUtc, 'H:i', $userTz);

        return view('book.confirm', compact('therapist', 'sessionType', 'dateLocal', 'localTime'));
    }

    /**
     * Paso Final: Crear Cita
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_email' => 'required|email|max:255',
        ]);

        // 1. Obtener sesión type (PRIMERO)
        $sessionType = SessionType::find(session('booking.session_type_id'));

        // 2. Preparar datos (DESPUÉS, usando $sessionType)
        $data = [
            'therapist_id' => session('booking.therapist_id'),
            'session_type_id' => session('booking.session_type_id'),
            'patient_name' => $validated['patient_name'],
            'patient_email' => $validated['patient_email'],
            'patient_timezone' => app('user.timezone'),
            'price' => $sessionType->price,
            'currency' => $sessionType->currency,
        ];

        // 3. Calcular fechas para la BD
        $startUtc = \Carbon\Carbon::parse(session('booking.starts_at_utc'), 'UTC');

        $data['starts_at'] = $startUtc->toDateTimeString();
        $data['ends_at'] = $startUtc->copy()->addMinutes($sessionType->duration_minutes)->toDateTimeString();

        try {
            $appointment = $this->bookingService->bookSlot($data);

            if (!$appointment) {
                throw ValidationException::withMessages([
                    'general' => __('app.error_booking_slot'),
                ]);
            }

            AppointmentBooked::dispatch($appointment);

            session()->forget(['booking.therapist_id', 'booking.session_type_id', 'booking.date', 'booking.starts_at_utc']);

            return Redirect::route('book.success');

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Booking error: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'general' => __('app.error_booking_slot'),
            ]);
        }
    }

    public function success()
    {
        return view('book.success');
    }

    /**
     * API para obtener slots (Usada por Alpine.js)
     */
    public function getSlotsApi(Request $request)
    {
        $request->validate([
            'therapist_id' => 'required|integer|exists:therapists,id',
            'date' => 'required|date_format:Y-m-d',
            'duration' => 'required|integer'
        ]);

        $therapist = Therapist::find($request->therapist_id);
        
        // Generar slots
        $slots = app(\App\Services\SlotGenerationService::class)->generate(
            $therapist, 
            $request->date, 
            $request->duration
        );

        return response()->json($slots);
    }
}
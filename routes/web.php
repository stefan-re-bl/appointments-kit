<?php

use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\AppointmentReportController;
use App\Http\Controllers\Admin\TherapistController as AdminTherapistController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAppointmentCancellationController;
use App\Http\Controllers\PublicAppointmentController;
use App\Http\Controllers\PublicAppointmentRescheduleController;
use App\Http\Controllers\PublicAppointmentRescheduleStoreController;
use App\Http\Controllers\SessionTypeController;
use App\Http\Controllers\Therapist\AppointmentIndexController;
use App\Http\Controllers\Therapist\AppointmentPaymentController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// --- Página pública "Mi Cita" (Ticket #11) ---
Route::get('/appointment/{token}', PublicAppointmentController::class)
    ->name('appointments.public.show');

// --- Link firmado de reprogramación pública (Ticket #18) ---
Route::get('/appointment/{token}/reschedule', PublicAppointmentRescheduleController::class)
    ->middleware(['signed', 'throttle:booking'])
    ->name('appointments.public.reschedule');

// --- Confirmación de reprogramación pública (Ticket #19) ---
Route::post('/appointment/{token}/reschedule', PublicAppointmentRescheduleStoreController::class)
    ->middleware(['signed', 'throttle:booking'])
    ->name('appointments.public.reschedule.store');

// --- Cancelación pública por token (Ticket #17) ---
Route::post('/appointment/{token}/cancel', PublicAppointmentCancellationController::class)
    ->middleware('throttle:booking')
    ->name('appointments.public.cancel');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('session-types', SessionTypeController::class);
    Route::resource('availabilities', AvailabilityController::class)->except(['show', 'edit', 'update']);
});

// --- Panel Administrativo: Visibilidad Global y Reportes (Tickets #21 y #22) ---
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', fn (): RedirectResponse => redirect()->route('admin.appointments.index'))
            ->name('index');

        Route::resource('therapists', AdminTherapistController::class)
            ->only(['index', 'edit', 'update']);

        Route::get('/appointments', [AdminAppointmentController::class, 'index'])
            ->name('appointments.index');

        Route::get('/reports/appointments', [AppointmentReportController::class, 'index'])
            ->name('reports.appointments.index');

        Route::get('/reports/appointments/export', [AppointmentReportController::class, 'export'])
            ->name('reports.appointments.export');
    });

// --- Panel de Terapeuta: Gestión de Citas y Pagos Manuales (Ticket #20) ---
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/therapist/appointments', AppointmentIndexController::class)
        ->name('therapist.appointments.index');

    Route::patch('/therapist/appointments/{appointment}/payment', AppointmentPaymentController::class)
        ->name('therapist.appointments.payment.update');
});

// --- Rutas Públicas de Reserva (Ticket #10) ---
Route::prefix('book')->name('book.')->group(function (): void {
    Route::get('/', [BookingController::class, 'index'])->name('index');

    Route::post('/therapist', [BookingController::class, 'storeTherapist'])
        ->middleware('throttle:booking')
        ->name('store.therapist');

    Route::get('/session', [BookingController::class, 'session'])->name('session');

    Route::post('/session', [BookingController::class, 'storeSession'])
        ->middleware('throttle:booking')
        ->name('store.session');

    Route::get('/date', [BookingController::class, 'date'])->name('date');

    Route::post('/date', [BookingController::class, 'storeDate'])
        ->middleware('throttle:booking')
        ->name('store.date');

    Route::get('/time', [BookingController::class, 'time'])->name('time');

    Route::post('/time', [BookingController::class, 'storeTime'])
        ->middleware('throttle:booking')
        ->name('store.time');

    Route::get('/confirm', [BookingController::class, 'confirm'])->name('confirm');

    Route::post('/', [BookingController::class, 'store'])
        ->middleware('throttle:booking')
        ->name('store');

    Route::get('/success', [BookingController::class, 'success'])->name('success');
});

// Ruta API interna para Alpine.js
// Mapeada a BookingController para centralizar la lógica del flujo público.
// Se mantiene en 'web' para acceder a cookies (user_timezone) y sesión.
Route::get('/api/slots', [BookingController::class, 'getSlotsApi'])
    ->middleware('throttle:booking')
    ->name('api.slots.index');

require __DIR__.'/auth.php';

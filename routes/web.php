<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\AppointmentReportController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\TherapistController as AdminTherapistController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAppointmentCancellationController;
use App\Http\Controllers\PublicAppointmentController;
use App\Http\Controllers\PublicAppointmentRescheduleController;
use App\Http\Controllers\PublicAppointmentRescheduleStoreController;
use App\Http\Controllers\PublicTherapistProfileController;
use App\Http\Controllers\SessionTypeController;
use App\Http\Controllers\Therapist\AppointmentIndexController;
use App\Http\Controllers\Therapist\AppointmentPaymentController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::view('/how-it-works', 'information.show', ['page' => 'how_it_works'])
    ->name('information.how-it-works');

Route::view('/faq', 'information.show', ['page' => 'faq'])
    ->name('information.faq');

Route::view('/patients', 'information.show', ['page' => 'patients'])
    ->name('information.patients');

Route::view('/payment-and-cancellation', 'information.show', ['page' => 'payment_and_cancellation'])
    ->name('information.payment-and-cancellation');

Route::view('/legal', 'legal.show', ['page' => 'index'])
    ->name('legal.index');

Route::view('/terms', 'legal.show', ['page' => 'terms'])
    ->name('legal.terms');

Route::view('/privacy', 'legal.show', ['page' => 'privacy'])
    ->name('legal.privacy');

Route::view('/emergency-notice', 'legal.show', ['page' => 'emergency'])
    ->name('legal.emergency-notice');

Route::get('/contact', [ContactMessageController::class, 'create'])
    ->name('contact.create');

Route::post('/contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/therapists/{slug}', PublicTherapistProfileController::class)
    ->name('therapists.show');

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

Route::get('/dashboard', DashboardController::class)->name('dashboard');

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

        Route::patch('/therapists/{therapist}/approve', [AdminTherapistController::class, 'approve'])
            ->name('therapists.approve');

        Route::patch('/therapists/{therapist}/revoke-approval', [AdminTherapistController::class, 'revokeApproval'])
            ->name('therapists.revoke-approval');

        Route::get('/appointments', [AdminAppointmentController::class, 'index'])
            ->name('appointments.index');

        Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])
            ->name('activity-logs.index');

        Route::get('/contact-messages', [AdminContactMessageController::class, 'index'])
            ->name('contact-messages.index');

        Route::patch('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'update'])
            ->name('contact-messages.update');

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
// Se mantiene en 'web' para compartir sesión y protecciones del flujo público.
Route::get('/api/slots', [BookingController::class, 'getSlotsApi'])
    ->middleware('throttle:booking')
    ->name('api.slots.index');

require __DIR__.'/auth.php';

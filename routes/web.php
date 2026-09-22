<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\AppointmentReportController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\ProfessionalController as AdminProfessionalController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Professional\AppointmentIndexController;
use App\Http\Controllers\Professional\AppointmentPaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAppointmentCancellationController;
use App\Http\Controllers\PublicAppointmentController;
use App\Http\Controllers\PublicAppointmentRescheduleController;
use App\Http\Controllers\PublicAppointmentRescheduleStoreController;
use App\Http\Controllers\PublicProfessionalDirectoryController;
use App\Http\Controllers\PublicProfessionalProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SessionTypeController;
use App\Http\Controllers\Webhooks\MetaWhatsAppWebhookController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::view('/how-it-works', 'information.show', ['page' => 'how_it_works'])
    ->middleware('feature:public_information_pages')
    ->name('information.how-it-works');

Route::view('/faq', 'information.show', ['page' => 'faq'])
    ->middleware(['feature:public_information_pages', 'feature:public_faq'])
    ->name('information.faq');

Route::redirect('/patients', '/customers', 301);

Route::view('/customers', 'information.show', ['page' => 'patients'])
    ->middleware('feature:public_information_pages')
    ->name('information.patients');

Route::view('/payment-and-cancellation', 'information.show', ['page' => 'payment_and_cancellation'])
    ->middleware('feature:public_information_pages')
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
    ->middleware('feature:public_contact_form')
    ->name('contact.create');

Route::post('/contact', [ContactMessageController::class, 'store'])
    ->middleware(['feature:public_contact_form', 'throttle:5,1'])
    ->name('contact.store');

Route::get('/webhooks/meta/whatsapp', [MetaWhatsAppWebhookController::class, 'verify'])
    ->middleware(['feature:whatsapp', 'throttle:60,1'])
    ->name('webhooks.meta-whatsapp.verify');

Route::post('/webhooks/meta/whatsapp', [MetaWhatsAppWebhookController::class, 'receive'])
    ->middleware(['feature:whatsapp', 'throttle:60,1'])
    ->name('webhooks.meta-whatsapp.receive');

Route::redirect('/therapists', '/professionals', 301);

Route::get('/therapists/{slug}', fn (string $slug): RedirectResponse => redirect()->route('professionals.show', $slug, 301));

Route::get('/professionals', PublicProfessionalDirectoryController::class)
    ->middleware('feature:public_provider_directory')
    ->name('professionals.index');

Route::get('/professionals/{slug}', PublicProfessionalProfileController::class)
    ->middleware('feature:public_provider_directory')
    ->name('professionals.show');

// --- Página pública "Mi Cita" (Ticket #11) ---
Route::get('/appointment/{token}', PublicAppointmentController::class)
    ->name('appointments.public.show');

// --- Link firmado de reprogramación pública (Ticket #18) ---
Route::get('/appointment/{token}/reschedule', PublicAppointmentRescheduleController::class)
    ->middleware(['feature:public_rescheduling', 'signed', 'throttle:booking'])
    ->name('appointments.public.reschedule');

Route::get('/appointment/{token}/reschedule/slots', [PublicAppointmentRescheduleController::class, 'slots'])
    ->middleware(['feature:public_rescheduling', 'signed', 'throttle:booking'])
    ->name('appointments.public.reschedule.slots');

// --- Confirmación de reprogramación pública (Ticket #19) ---
Route::post('/appointment/{token}/reschedule', PublicAppointmentRescheduleStoreController::class)
    ->middleware(['feature:public_rescheduling', 'signed', 'throttle:booking'])
    ->name('appointments.public.reschedule.store');

// --- Cancelación pública por token (Ticket #17) ---
Route::post('/appointment/{token}/cancel', PublicAppointmentCancellationController::class)
    ->middleware(['feature:public_cancellation', 'throttle:booking'])
    ->name('appointments.public.cancel');

Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('services', ServiceController::class)->except('show');
    Route::resource('session-types', SessionTypeController::class)->except('show');
    Route::resource('availabilities', AvailabilityController::class)->except(['show', 'edit', 'update']);
});

// --- Panel Administrativo: Visibilidad Global y Reportes (Tickets #21 y #22) ---
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', fn (): RedirectResponse => redirect()->route('admin.appointments.index'))
            ->name('index');

        Route::resource('professionals', AdminProfessionalController::class)
            ->parameters(['professionals' => 'professional'])
            ->names('professionals')
            ->only(['index', 'edit', 'update']);

        Route::patch('/professionals/{professional}/approve', [AdminProfessionalController::class, 'approve'])
            ->name('professionals.approve');

        Route::patch('/professionals/{professional}/revoke-approval', [AdminProfessionalController::class, 'revokeApproval'])
            ->name('professionals.revoke-approval');

        Route::get('/appointments', [AdminAppointmentController::class, 'index'])
            ->name('appointments.index');

        Route::get('/appointments/events', [AdminAppointmentController::class, 'events'])
            ->name('appointments.events');

        Route::get('/appointments/day', [AdminAppointmentController::class, 'day'])
            ->name('appointments.day');

        Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])
            ->middleware('feature:visible_audit')
            ->name('activity-logs.index');

        Route::get('/contact-messages', [AdminContactMessageController::class, 'index'])
            ->name('contact-messages.index');

        Route::patch('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'update'])
            ->name('contact-messages.update');

        Route::get('/reports/appointments', [AppointmentReportController::class, 'index'])
            ->middleware('feature:reports')
            ->name('reports.appointments.index');

        Route::get('/reports/appointments/export', [AppointmentReportController::class, 'export'])
            ->middleware('feature:reports')
            ->name('reports.appointments.export');
    });

// --- Panel profesional: Gestión de citas y pagos manuales (Ticket #20) ---
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::redirect('/therapist/appointments', '/professional/appointments', 301);

    Route::get('/professional/appointments', AppointmentIndexController::class)
        ->name('professional.appointments.index');

    Route::get('/professional/appointments/events', [AppointmentIndexController::class, 'events'])
        ->name('professional.appointments.events');

    Route::get('/professional/appointments/day', [AppointmentIndexController::class, 'day'])
        ->name('professional.appointments.day');

    Route::patch('/professional/appointments/{appointment}/payment', AppointmentPaymentController::class)
        ->middleware('feature:manual_payments')
        ->name('professional.appointments.payment.update');
});

// --- Carga interna de citas por profesionales ---
Route::prefix('book')->name('book.')->group(function (): void {
    Route::get('/', [BookingController::class, 'index'])->name('index');

    Route::post('/professional', [BookingController::class, 'storeProfessional'])
        ->middleware('throttle:booking')
        ->name('store.professional');

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

// Ruta API interna para Alpine.js.
// Mapeada a BookingController para centralizar la lógica de carga interna de turnos.
// Se mantiene en 'web' para compartir sesión y protecciones del flujo autenticado.
Route::get('/api/slots', [BookingController::class, 'getSlotsApi'])
    ->middleware('throttle:booking')
    ->name('api.slots.index');

require __DIR__.'/auth.php';

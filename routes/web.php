<?php

use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::resource('session-types', SessionTypeController::class);
    Route::resource('availabilities', AvailabilityController::class)->except(['show', 'edit', 'update']);
});

// --- Rutas Públicas de Reserva (Ticket #10) ---
Route::prefix('book')->name('book.')->group(function () {
    Route::get('/', [BookingController::class, 'index'])->name('index');
    Route::post('/therapist', [BookingController::class, 'storeTherapist'])->name('store.therapist');
    
    Route::get('/session', [BookingController::class, 'session'])->name('session');
    Route::post('/session', [BookingController::class, 'storeSession'])->name('store.session');
    
    Route::get('/date', [BookingController::class, 'date'])->name('date');
    Route::post('/date', [BookingController::class, 'storeDate'])->name('store.date');
    
    Route::get('/time', [BookingController::class, 'time'])->name('time');
    Route::post('/time', [BookingController::class, 'storeTime'])->name('store.time');
    
    Route::get('/confirm', [BookingController::class, 'confirm'])->name('confirm');
    Route::post('/', [BookingController::class, 'store'])->name('store');
    
    Route::get('/success', [BookingController::class, 'success'])->name('success');
});

// Ruta API interna para Alpine.js
// Mapeada a BookingController para centralizar la lógica del flujo público.
// Se mantiene en 'web' para acceder a cookies (user_timezone) y sesión.
Route::get('/api/slots', [BookingController::class, 'getSlotsApi'])->name('api.slots.index');

require __DIR__.'/auth.php';
<?php

use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionTypeController;
use App\Http\Controllers\SlotController;
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

// Ruta API interna para Alpine.js
// Registrada en web.php para compartir cookies/middleware de sesión (SetTimezone, SetLocale).
// Sin middleware 'auth' para permitir su consumo en el futuro flujo público de reservas (Ticket #10).
Route::get('/api/slots', [SlotController::class, 'index'])->name('api.slots.index');

require __DIR__.'/auth.php';
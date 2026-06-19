<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final class PublicAppointmentRescheduleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('signed'),
        ];
    }

    public function __invoke(string $token): RedirectResponse
    {
        return redirect()
            ->route('appointments.public.show', ['token' => $token])
            ->with('public_action', 'reschedule');
    }
}
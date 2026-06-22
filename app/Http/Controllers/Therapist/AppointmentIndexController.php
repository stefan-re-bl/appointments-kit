<?php

declare(strict_types=1);

namespace App\Http\Controllers\Therapist;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Services\TimezoneService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AppointmentIndexController extends Controller implements HasMiddleware
{
    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Request $request, TimezoneService $timezoneService): View
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null || $user->therapist === null) {
            abort(403);
        }

        $therapist = $user->therapist;

        app()->instance('user.timezone', $therapist->timezone);

        $appointments = Appointment::query()
            ->with(['therapist.user', 'sessionType'])
            ->where('therapist_id', $therapist->id)
            ->orderByDesc('starts_at')
            ->paginate(15);

        return view('therapist.appointments.index', [
            'appointments' => $appointments,
            'paymentStatuses' => PaymentStatus::cases(),
            'timezoneService' => $timezoneService,
            'therapistTimezone' => $therapist->timezone,
        ]);
    }
}
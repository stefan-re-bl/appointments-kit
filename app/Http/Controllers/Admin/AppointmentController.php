<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterAppointmentsRequest;
use App\Models\Appointment;
use App\Models\Therapist;
use App\Services\TimezoneService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final class AppointmentController extends Controller implements HasMiddleware
{
    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('admin'),
        ];
    }

    public function index(FilterAppointmentsRequest $request, TimezoneService $timezoneService): View
    {
        $filters = $request->validated();

        $timezone = app()->bound('user.timezone')
            ? (string) app('user.timezone')
            : 'UTC';

        $query = Appointment::query()
            ->with(['therapist.user', 'sessionType'])
            ->when(! empty($filters['therapist_id']), function (Builder $query) use ($filters): void {
                $query->where('therapist_id', $filters['therapist_id']);
            })
            ->when(! empty($filters['status']), function (Builder $query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when(! empty($filters['payment_status']), function (Builder $query) use ($filters): void {
                $query->where('payment_status', $filters['payment_status']);
            });

        if (! empty($filters['date_from'])) {
            $query->where(
                'starts_at',
                '>=',
                $timezoneService->toUtc($filters['date_from'] . ' 00:00:00', $timezone)
            );
        }

        if (! empty($filters['date_to'])) {
            $query->where(
                'starts_at',
                '<=',
                $timezoneService->toUtc($filters['date_to'] . ' 23:59:59', $timezone)
            );
        }

        $appointments = $query
            ->orderByDesc('starts_at')
            ->paginate(20)
            ->withQueryString();

        $today = CarbonImmutable::now($timezone)->toDateString();

        $todayStartUtc = $timezoneService->toUtc($today . ' 00:00:00', $timezone);
        $todayEndUtc = $timezoneService->toUtc($today . ' 23:59:59', $timezone);

        $summary = [
            'total' => Appointment::query()->count(),
            'confirmed' => Appointment::query()
                ->where('status', AppointmentStatus::CONFIRMED->value)
                ->count(),
            'pending_payment' => Appointment::query()
                ->where('payment_status', PaymentStatus::PENDING->value)
                ->count(),
            'today' => Appointment::query()
                ->whereBetween('starts_at', [$todayStartUtc, $todayEndUtc])
                ->count(),
        ];

        $therapists = Therapist::query()
            ->with('user')
            ->orderBy('id')
            ->get();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'therapists' => $therapists,
            'appointmentStatuses' => AppointmentStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
            'filters' => $filters,
            'summary' => $summary,
            'timezoneService' => $timezoneService,
        ]);
    }
}
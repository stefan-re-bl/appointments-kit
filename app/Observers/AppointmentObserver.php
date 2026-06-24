<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Appointment;
use App\Services\ActivityLogService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class AppointmentObserver
{
    public function updated(Appointment $appointment): void
    {
        $activityLogService = app(ActivityLogService::class);

        /** @var Authenticatable|null $causer */
        $causer = Auth::guard()->user();

        if ($appointment->wasChanged('status')) {
            $activityLogService->recordAppointmentStatusChange(
                appointment: $appointment,
                from: $appointment->getRawOriginal('status'),
                to: $appointment->status,
                causer: $causer,
                metadata: [
                    'changed_fields' => array_keys($appointment->getChanges()),
                ],
            );
        }

        if ($appointment->wasChanged('payment_status') || $appointment->wasChanged('paid_at')) {
            $activityLogService->recordAppointmentPaymentUpdate(
                appointment: $appointment,
                paymentStatusFrom: $appointment->getRawOriginal('payment_status'),
                paymentStatusTo: $appointment->payment_status,
                paidAtFrom: $appointment->getRawOriginal('paid_at'),
                paidAtTo: $appointment->paid_at,
                causer: $causer,
                metadata: [
                    'changed_fields' => array_keys($appointment->getChanges()),
                ],
            );
        }
    }
}
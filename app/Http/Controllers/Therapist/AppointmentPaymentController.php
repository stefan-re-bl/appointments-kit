<?php

declare(strict_types=1);

namespace App\Http\Controllers\Therapist;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAppointmentPaymentRequest;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AppointmentPaymentController extends Controller implements HasMiddleware
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

    public function __invoke(UpdateAppointmentPaymentRequest $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validated();

        $newPaymentStatus = PaymentStatus::from($validated['payment_status']);
        $currentPaymentStatus = $appointment->payment_status instanceof PaymentStatus
            ? $appointment->payment_status
            : PaymentStatus::from((string) $appointment->payment_status);

        $appointment->payment_status = $newPaymentStatus;

        if ($newPaymentStatus === PaymentStatus::PAID) {
            $appointment->paid_at = $currentPaymentStatus === PaymentStatus::PAID && $appointment->paid_at !== null
                ? $appointment->paid_at
                : CarbonImmutable::now('UTC');
        } else {
            $appointment->paid_at = null;
        }

        $appointment->save();

        return back()->with('success', __('app.payments.updated'));
    }
}
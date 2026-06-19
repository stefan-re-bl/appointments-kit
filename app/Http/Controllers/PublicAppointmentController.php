<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Services\CancellationPolicyService;
use App\Services\TimezoneService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;

final class PublicAppointmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [];
    }

    public function __invoke(
        string $token,
        TimezoneService $timezoneService,
        CancellationPolicyService $cancellationPolicyService,
    ): View|Response {
        $appointment = Appointment::query()
            ->with(['therapist.user', 'sessionType'])
            ->where('token', $token)
            ->first();

        if (! $appointment instanceof Appointment) {
            return response()->view('appointments.public-not-found', status: 404);
        }

        $patientTimezone = $this->resolvePatientTimezone($appointment->patient_timezone);

        app()->instance('user.timezone', $patientTimezone);

        $startsAt = $timezoneService->formatForDisplay($appointment->starts_at, 'd/m/Y H:i');
        $endsAt = $timezoneService->formatForDisplay($appointment->ends_at, 'd/m/Y H:i');
        $paidAt = $appointment->paid_at
            ? $timezoneService->formatForDisplay($appointment->paid_at, 'd/m/Y H:i')
            : null;

        $appointmentStatusLabel = $this->appointmentStatusLabel($appointment->status);
        $paymentStatusLabel = $this->paymentStatusLabel($appointment->payment_status);

        $canJoinMeet = $appointment->status === AppointmentStatus::CONFIRMED
            && filled($appointment->therapist?->google_meet_link);

        $canContactTherapist = in_array(
            $appointment->status,
            [AppointmentStatus::PENDING, AppointmentStatus::CONFIRMED],
            true
        ) && filled($appointment->therapist?->user?->email);

        $canRefund = $cancellationPolicyService->canRefund($appointment);
        $canCancel = $cancellationPolicyService->canCancel($appointment);
        $canReschedule = $cancellationPolicyService->canReschedule($appointment);

        $policyMessage = __($cancellationPolicyService->messageKey($appointment), [
            'refund_hours' => $cancellationPolicyService->refundDeadlineHours(),
            'reschedule_hours' => $cancellationPolicyService->rescheduleDeadlineHours(),
            'max_reschedules' => $cancellationPolicyService->maxReschedules(),
        ]);

        return view('appointments.public-show', [
            'appointment' => $appointment,
            'patientTimezone' => $patientTimezone,
            'startsAt' => $startsAt,
            'endsAt' => $endsAt,
            'paidAt' => $paidAt,
            'appointmentStatusLabel' => $appointmentStatusLabel,
            'paymentStatusLabel' => $paymentStatusLabel,
            'canJoinMeet' => $canJoinMeet,
            'canContactTherapist' => $canContactTherapist,
            'canRefund' => $canRefund,
            'canCancel' => $canCancel,
            'canReschedule' => $canReschedule,
            'policyMessage' => $policyMessage,
            'rescheduleMailto' => $this->buildRescheduleMailto($appointment),
        ]);
    }

    private function resolvePatientTimezone(?string $timezone): string
    {
        if (is_string($timezone) && in_array($timezone, timezone_identifiers_list(), true)) {
            return $timezone;
        }

        return 'UTC';
    }

    private function appointmentStatusLabel(AppointmentStatus $status): string
    {
        return match ($status) {
            AppointmentStatus::PENDING => __('app.appointment_public.status.pending'),
            AppointmentStatus::CONFIRMED => __('app.appointment_public.status.confirmed'),
            AppointmentStatus::CANCELLED => __('app.appointment_public.status.cancelled'),
            AppointmentStatus::COMPLETED => __('app.appointment_public.status.completed'),
        };
    }

    private function paymentStatusLabel(PaymentStatus $status): string
    {
        return match ($status) {
            PaymentStatus::PENDING => __('app.appointment_public.payment.pending'),
            PaymentStatus::PAID => __('app.appointment_public.payment.paid'),
            PaymentStatus::WAIVED => __('app.appointment_public.payment.waived'),
        };
    }

    private function buildRescheduleMailto(Appointment $appointment): ?string
    {
        $therapistEmail = $appointment->therapist?->user?->email;

        if (! filled($therapistEmail)) {
            return null;
        }

        $subject = __('appointment_policy.mail.reschedule_subject');

        $body = __('appointment_policy.mail.reschedule_body', [
            'patient' => $appointment->patient_name,
            'url' => route('appointments.public.show', $appointment->token),
        ]);

        return sprintf(
            'mailto:%s?subject=%s&body=%s',
            rawurlencode((string) $therapistEmail),
            rawurlencode($subject),
            rawurlencode($body),
        );
    }
}
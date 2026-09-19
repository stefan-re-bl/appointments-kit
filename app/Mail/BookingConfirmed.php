<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Appointment;
use App\Services\TimezoneService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class BookingConfirmed extends Mailable
{
    use Queueable;
    use SerializesModels;

    public const RECIPIENT_PATIENT = 'patient';

    public const RECIPIENT_PROFESSIONAL = 'professional';

    public function __construct(
        public readonly Appointment $appointment,
        public readonly string $recipientType,
    ) {
        $this->appointment->loadMissing(['professional.user', 'sessionType']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('app.emails.booking_confirmed.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking-confirmed',
            with: $this->viewData(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(): array
    {
        $displayTimezone = $this->displayTimezone();

        $previousTimezone = app()->bound('user.timezone')
            ? app('user.timezone')
            : null;

        app()->instance('user.timezone', $displayTimezone);

        /** @var TimezoneService $timezoneService */
        $timezoneService = app(TimezoneService::class);

        $startsAt = $timezoneService->formatForDisplay(
            $this->appointment->starts_at,
            'd/m/Y H:i'
        );

        $endsAt = $timezoneService->formatForDisplay(
            $this->appointment->ends_at,
            'd/m/Y H:i'
        );

        if (is_string($previousTimezone)) {
            app()->instance('user.timezone', $previousTimezone);
        } else {
            app()->forgetInstance('user.timezone');
        }

        return [
            'appointment' => $this->appointment,
            'recipientType' => $this->recipientType,
            'startsAt' => $startsAt,
            'endsAt' => $endsAt,
            'displayTimezone' => $displayTimezone,
            'appointmentUrl' => route('appointments.public.show', $this->appointment->token),
            'meetLink' => $this->appointment->professional?->google_meet_link,
            'paymentInstructions' => $this->appointment->professional?->payment_instructions,
            'professionalName' => $this->appointment->professional?->user?->name,
            'patientName' => $this->appointment->patient_name,
            'sessionTypeName' => $this->appointment->sessionType?->name,
        ];
    }

    private function displayTimezone(): string
    {
        if ($this->recipientType === self::RECIPIENT_PROFESSIONAL) {
            return $this->appointment->professional?->timezone ?: 'UTC';
        }

        return $this->appointment->patient_timezone ?: 'UTC';
    }

    /**
     * @return array<int, mixed>
     */
    public function attachments(): array
    {
        return [];
    }
}

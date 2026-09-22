<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Appointment;
use App\Services\TimezoneService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentCancelled extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private const DISPLAY_FORMAT = 'd/m/Y H:i';

    public const RECIPIENT_CUSTOMER = 'patient';

    public const RECIPIENT_PROFESSIONAL = 'professional';

    public function __construct(
        public Appointment $appointment,
        public string $recipientTimezone,
        public string $recipientType,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('appointment_emails.cancelled.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointments.cancelled',
            with: $this->viewData(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $this->appointment->loadMissing(['professional.user', 'service']);

        return [
            'appointment' => $this->appointment,
            'recipientName' => $this->recipientName(),
            'professionalName' => $this->appointment->professional->user->name,
            'patientName' => $this->appointment->customer_name,
            'sessionTypeName' => $this->appointment->service->name,
            'appointmentRange' => $this->formatRangeForRecipient(
                $this->appointment->starts_at,
                $this->appointment->ends_at,
            ),
            'recipientTimezone' => $this->recipientTimezone,
            'publicUrl' => route('appointments.public.show', $this->appointment->token),
        ];
    }

    private function recipientName(): string
    {
        if ($this->recipientType === self::RECIPIENT_PROFESSIONAL) {
            return $this->appointment->professional->user->name;
        }

        return $this->appointment->customer_name;
    }

    private function formatRangeForRecipient(CarbonInterface|string $startsAt, CarbonInterface|string $endsAt): string
    {
        return __('appointment_emails.common.time_range', [
            'start' => $this->formatForRecipient($startsAt),
            'end' => $this->formatForRecipient($endsAt),
        ]);
    }

    private function formatForRecipient(CarbonInterface|string $date): string
    {
        return app(TimezoneService::class)->formatForDisplay(
            datetime: $this->normalizeDateForTimezoneService($date),
            format: self::DISPLAY_FORMAT,
            toTimezone: $this->recipientTimezone,
        );
    }

    private function normalizeDateForTimezoneService(CarbonInterface|string $date): Carbon|string
    {
        if (is_string($date)) {
            return $date;
        }

        return Carbon::instance($date);
    }
}

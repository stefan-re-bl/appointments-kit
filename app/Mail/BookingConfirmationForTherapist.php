<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Services\TimezoneService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationForTherapist extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('app.mail_therapist_subject'),
        );
    }

    protected function formatDate(): string
    {
        $tz = $this->appointment->therapist?->timezone
            ?? $this->appointment->patient_timezone;

        return app(TimezoneService::class)->formatForDisplay(
            $this->appointment->starts_at,
            'd/m/Y H:i',
            $tz,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.therapist_booking',
            with: [
                'patientName' => $this->appointment->patient_name,
                'localDate' => $this->formatDate(),
            ],
        );
    }
}

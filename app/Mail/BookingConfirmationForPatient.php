<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Services\TimezoneService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationForPatient extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('app.mail_patient_subject'),
        );
    }

    public function content(): Content
    {
        $tz = $this->appointment->patient_timezone;
        $localDate = app(TimezoneService::class)->formatForDisplay(
            $this->appointment->starts_at,
            'd/m/Y H:i',
            $tz,
        );

        return new Content(
            markdown: 'emails.patient_booking',
            with: ['localDate' => $localDate],
        );
    }
}

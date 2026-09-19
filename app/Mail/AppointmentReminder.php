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

final class AppointmentReminder extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Appointment $appointment,
    ) {
        $this->appointment->loadMissing(['professional.user', 'sessionType']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('reminders.appointment.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointment-reminder',
            with: $this->viewData(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(): array
    {
        $timezone = $this->appointment->patient_timezone ?: 'UTC';

        [$startsAt, $endsAt] = $this->formattedRange($timezone);

        return [
            'appointment' => $this->appointment,
            'startsAt' => $startsAt,
            'endsAt' => $endsAt,
            'timezone' => $timezone,
            'publicUrl' => route('appointments.public.show', [
                'token' => $this->appointment->token,
            ]),
            'googleMeetLink' => $this->appointment->professional->google_meet_link,
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function formattedRange(string $timezone): array
    {
        $timezoneService = app(TimezoneService::class);

        $previousTimezone = app()->bound('user.timezone')
            ? (string) app('user.timezone')
            : (string) config('app.timezone', 'UTC');

        app()->instance('user.timezone', $timezone);

        try {
            return [
                $timezoneService->formatForDisplay($this->appointment->starts_at, 'd/m/Y H:i'),
                $timezoneService->formatForDisplay($this->appointment->ends_at, 'd/m/Y H:i'),
            ];
        } finally {
            app()->instance('user.timezone', $previousTimezone);
        }
    }

    /**
     * @return array<int, mixed>
     */
    public function attachments(): array
    {
        return [];
    }
}

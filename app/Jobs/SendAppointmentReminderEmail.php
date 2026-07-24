<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendAppointmentReminderEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        private readonly int $appointmentId,
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(): void
    {
        $appointment = Appointment::query()
            ->with(['therapist.user', 'sessionType'])
            ->find($this->appointmentId);

        if (! $appointment instanceof Appointment) {
            return;
        }

        if ($appointment->status !== AppointmentStatus::CONFIRMED || $appointment->reminder_sent_at !== null) {
            $appointment->forceFill([
                'reminder_queued_at' => null,
            ])->save();

            return;
        }

        if ($appointment->starts_at->utc()->lte(CarbonImmutable::now('UTC'))) {
            $appointment->forceFill([
                'reminder_queued_at' => null,
            ])->save();

            return;
        }

        $appointment->increment('reminder_attempts');

        Mail::to($appointment->patient_email)->send(
            new AppointmentReminder($appointment)
        );

        $appointment->forceFill([
            'reminder_sent_at' => CarbonImmutable::now('UTC')->toDateTimeString(),
            'reminder_queued_at' => null,
            'reminder_failed_at' => null,
        ])->save();
    }

    public function failed(?Throwable $exception): void
    {
        Appointment::query()
            ->whereKey($this->appointmentId)
            ->whereNull('reminder_sent_at')
            ->update([
                'reminder_queued_at' => null,
                'reminder_failed_at' => CarbonImmutable::now('UTC')->toDateTimeString(),
            ]);
    }
}

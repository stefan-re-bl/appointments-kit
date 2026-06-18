<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders {--dry-run : Count due reminders without sending emails}';

    protected $description = 'Send appointment reminder emails one hour before confirmed sessions.';

    public function handle(): int
    {
        $now = CarbonImmutable::now('UTC');
        $dueUntil = $now->addHour();

        $appointments = Appointment::query()
            ->with(['therapist.user', 'sessionType'])
            ->where('status', AppointmentStatus::CONFIRMED->value)
            ->whereNull('reminder_sent_at')
            ->where('starts_at', '>', $now->toDateTimeString())
            ->where('starts_at', '<=', $dueUntil->toDateTimeString())
            ->orderBy('starts_at')
            ->get();

        if ($this->option('dry-run')) {
            $this->info("Due appointment reminders: {$appointments->count()}");

            return Command::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($appointments as $appointment) {
            $claimed = Appointment::query()
                ->whereKey($appointment->getKey())
                ->where('status', AppointmentStatus::CONFIRMED->value)
                ->whereNull('reminder_sent_at')
                ->where('starts_at', '>', $now->toDateTimeString())
                ->where('starts_at', '<=', $dueUntil->toDateTimeString())
                ->update([
                    'reminder_sent_at' => CarbonImmutable::now('UTC')->toDateTimeString(),
                ]);

            if ($claimed !== 1) {
                $skipped++;

                continue;
            }

            $freshAppointment = $appointment->fresh(['therapist.user', 'sessionType']);

            if (!$freshAppointment instanceof Appointment) {
                $failed++;
                $this->error("Appointment {$appointment->getKey()} could not be refreshed.");

                continue;
            }

            try {
                Mail::to($freshAppointment->patient_email)->send(
                    new AppointmentReminder($freshAppointment)
                );

                $sent++;
            } catch (Throwable $exception) {
                Appointment::query()
                    ->whereKey($freshAppointment->getKey())
                    ->update([
                        'reminder_sent_at' => null,
                    ]);

                report($exception);

                $failed++;
                $this->error("Failed to send reminder for appointment {$freshAppointment->getKey()}.");
            }
        }

        $this->info("Appointment reminders sent: {$sent}");
        $this->info("Appointment reminders skipped: {$skipped}");

        if ($failed > 0) {
            $this->error("Appointment reminders failed: {$failed}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Jobs\SendAppointmentReminderEmail;
use App\Models\Appointment;
use App\Services\Notifications\WhatsAppDeliveryDispatcher;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

final class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders {--dry-run : Count due reminders without sending emails}';

    protected $description = 'Queue appointment reminder emails before confirmed appointments.';

    public function handle(WhatsAppDeliveryDispatcher $whatsAppDeliveryDispatcher): int
    {
        $now = CarbonImmutable::now('UTC');
        $dueUntil = $now->addHours($this->reminderLeadHours());
        $staleQueuedAt = $now->subMinutes($this->staleQueueMinutes());

        $appointments = Appointment::query()
            ->where('status', AppointmentStatus::CONFIRMED->value)
            ->whereNull('reminder_sent_at')
            ->where(function ($query) use ($staleQueuedAt): void {
                $query
                    ->whereNull('reminder_queued_at')
                    ->orWhere('reminder_queued_at', '<=', $staleQueuedAt->toDateTimeString());
            })
            ->where('starts_at', '>', $now->toDateTimeString())
            ->where('starts_at', '<=', $dueUntil->toDateTimeString())
            ->orderBy('starts_at')
            ->get();

        if ($this->option('dry-run')) {
            $this->info("Due appointment reminders to queue: {$appointments->count()}");

            return Command::SUCCESS;
        }

        $queued = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($appointments as $appointment) {
            $claimed = Appointment::query()
                ->whereKey($appointment->getKey())
                ->where('status', AppointmentStatus::CONFIRMED->value)
                ->whereNull('reminder_sent_at')
                ->where(function ($query) use ($staleQueuedAt): void {
                    $query
                        ->whereNull('reminder_queued_at')
                        ->orWhere('reminder_queued_at', '<=', $staleQueuedAt->toDateTimeString());
                })
                ->where('starts_at', '>', $now->toDateTimeString())
                ->where('starts_at', '<=', $dueUntil->toDateTimeString())
                ->update([
                    'reminder_queued_at' => CarbonImmutable::now('UTC')->toDateTimeString(),
                ]);

            if ($claimed !== 1) {
                $skipped++;

                continue;
            }

            try {
                SendAppointmentReminderEmail::dispatch($appointment->getKey());
                $whatsAppDeliveryDispatcher->dispatchReminder($appointment);
                $queued++;
            } catch (Throwable $exception) {
                Appointment::query()
                    ->whereKey($appointment->getKey())
                    ->update([
                        'reminder_queued_at' => null,
                        'reminder_failed_at' => CarbonImmutable::now('UTC')->toDateTimeString(),
                    ]);

                report($exception);

                $failed++;
                $this->error("Failed to queue reminder for appointment {$appointment->getKey()}.");
            }
        }

        $this->info("Appointment reminders queued: {$queued}");
        $this->info("Appointment reminders skipped: {$skipped}");

        if ($failed > 0) {
            $this->error("Appointment reminders failed to queue: {$failed}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function reminderLeadHours(): int
    {
        return max(1, (int) config('booking.reminders.lead_hours', 24));
    }

    private function staleQueueMinutes(): int
    {
        return max(1, (int) config('booking.reminders.stale_queue_minutes', 15));
    }
}

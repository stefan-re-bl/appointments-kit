<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CleanupExpiredAppointments extends Command
{
    protected $signature = 'appointments:cleanup-expired {--dry-run : Count expired pending appointments without cancelling them}';

    protected $description = 'Cancel expired pending appointments to release abandoned booking slots.';

    public function handle(): int
    {
        $expiredPendingAppointments = Appointment::query()
            ->expiredPending()
            ->orderBy('id');

        $count = (clone $expiredPendingAppointments)->count();

        if ($this->option('dry-run')) {
            $this->info("Expired pending appointments found: {$count}");

            return self::SUCCESS;
        }

        $cancelled = 0;

        $expiredPendingAppointments->chunkById(100, function (Collection $appointments) use (&$cancelled): void {
            foreach ($appointments as $appointment) {
                $appointment->forceFill([
                    'status' => AppointmentStatus::CANCELLED,
                ])->save();

                $cancelled++;
            }
        });

        $this->info("Expired pending appointments cancelled: {$cancelled}");

        return self::SUCCESS;
    }
}
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class CleanupExpiredAppointments extends Command
{
    protected $signature = 'appointments:cleanup-expired {--dry-run : Count expired pending appointments without cancelling them}';

    protected $description = 'Cancel expired pending appointments to release abandoned booking slots.';

    public function handle(): int
    {
        $expiredPendingAppointments = Appointment::query()
            ->expiredPending();

        $count = (clone $expiredPendingAppointments)->count();

        if ($this->option('dry-run')) {
            $this->info("Expired pending appointments found: {$count}");

            return self::SUCCESS;
        }

        $updated = $expiredPendingAppointments->update([
            'status' => AppointmentStatus::CANCELLED->value,
            'updated_at' => CarbonImmutable::now('UTC'),
        ]);

        $this->info("Expired pending appointments cancelled: {$updated}");

        return self::SUCCESS;
    }
}
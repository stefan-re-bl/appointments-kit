<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Appointment;
use App\Notifications\QueueBusyNotification;
use App\Notifications\QueueJobFailedNotification;
use App\Observers\AppointmentObserver;
use App\Services\BookingService;
use App\Services\TimezoneService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\QueueBusy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registramos el servicio de zonas horarias como Singleton
        $this->app->singleton(TimezoneService::class, function (): TimezoneService {
            return new TimezoneService();
        });

        // Servicio de reservas con prevención de race conditions (Ticket #9)
        $this->app->singleton(BookingService::class);
    }

    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);

        RateLimiter::for('booking', function (Request $request): array {
            $limits = [
                Limit::perMinute(30)->by('booking:ip:' . $request->ip()),
            ];

            $patientEmail = $request->input('patient_email');

            if (is_string($patientEmail) && $patientEmail !== '') {
                $limits[] = Limit::perHour(10)->by(
                    'booking:email:' . Str::lower($patientEmail) . '|ip:' . $request->ip()
                );
            }

            return $limits;
        });

        Event::listen(function (QueueBusy $event): void {
            Log::warning('Queue busy threshold exceeded.', [
                'connection' => $event->connectionName,
                'queue' => $event->queue,
                'size' => $event->size,
            ]);

            $email = $this->monitoringAlertEmail();

            if ($email === null) {
                return;
            }

            $cacheKey = sprintf(
                'monitoring:queue-busy-alert-sent:%s:%s',
                $event->connectionName,
                $event->queue,
            );

            if (! Cache::add($cacheKey, true, now('UTC')->addMinutes($this->alertCooldownMinutes()))) {
                return;
            }

            Notification::route('mail', $email)->notify(
                new QueueBusyNotification(
                    $event->connectionName,
                    $event->queue,
                    $event->size,
                ),
            );
        });

        Queue::failing(function (JobFailed $event): void {
            $jobName = $event->job->resolveName();

            Log::error('Queued job failed.', [
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
                'job' => $jobName,
                'job_id' => $event->job->getJobId(),
                'exception' => $event->exception,
            ]);

            $email = $this->monitoringAlertEmail();

            if ($email === null) {
                return;
            }

            Notification::route('mail', $email)->notify(
                new QueueJobFailedNotification(
                    $event->connectionName,
                    $event->job->getQueue() ?? 'default',
                    $jobName,
                    $event->exception::class,
                    $event->exception->getMessage(),
                ),
            );
        });
    }

    private function monitoringAlertEmail(): ?string
    {
        $email = trim((string) config('monitoring.alerts.email', ''));

        return $email === '' ? null : $email;
    }

    private function alertCooldownMinutes(): int
    {
        return max(1, (int) config('monitoring.queue.alert_cooldown_minutes', 30));
    }
}
<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('appointments:cleanup-expired')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('appointments:send-reminders')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

if ((bool) config('monitoring.queue.enabled', true)) {
    $queueConnection = (string) config('monitoring.queue.connection', 'redis');
    $queueName = (string) config('monitoring.queue.queue', 'default');
    $maxJobs = max(1, (int) config('monitoring.queue.max_jobs', 25));

    Schedule::command('monitoring:queue-heartbeat')
        ->everyMinute()
        ->withoutOverlapping()
        ->onOneServer();

    Schedule::command('monitoring:queue-worker')
        ->everyMinute()
        ->withoutOverlapping()
        ->onOneServer();

    Schedule::command("queue:monitor {$queueConnection}:{$queueName} --max={$maxJobs}")
        ->everyMinute()
        ->withoutOverlapping()
        ->onOneServer();
}
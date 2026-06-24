<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\QueueWorkerHeartbeat;
use App\Notifications\QueueWorkerDownNotification;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class QueueWorkerMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_heartbeat_job_updates_cache(): void
    {
        Config::set('monitoring.queue.heartbeat_cache_key', 'test:queue-heartbeat');
        Config::set('monitoring.queue.heartbeat_ttl_minutes', 10);

        (new QueueWorkerHeartbeat(
            connectionName: 'redis',
            queueName: 'default',
        ))->handle();

        $this->assertNotNull(Cache::get('test:queue-heartbeat:redis:default'));
    }

    public function test_monitoring_command_alerts_when_heartbeat_is_missing(): void
    {
        Notification::fake();

        Config::set('monitoring.queue.enabled', true);
        Config::set('monitoring.alerts.email', 'admin@example.com');
        Config::set('monitoring.queue.heartbeat_cache_key', 'test:queue-heartbeat');
        Config::set('monitoring.queue.alert_cooldown_minutes', 30);

        Cache::forget('test:queue-heartbeat:redis:default');
        Cache::forget('monitoring:queue-worker-alert-sent:redis:default');

        $this->artisan('monitoring:queue-worker', [
            '--connection' => 'redis',
            '--queue' => 'default',
        ])->assertFailed();

        Notification::assertSentOnDemand(QueueWorkerDownNotification::class);
    }

    public function test_monitoring_command_passes_when_heartbeat_is_recent(): void
    {
        Notification::fake();

        Config::set('monitoring.queue.enabled', true);
        Config::set('monitoring.alerts.email', 'admin@example.com');
        Config::set('monitoring.queue.heartbeat_cache_key', 'test:queue-heartbeat');
        Config::set('monitoring.queue.stale_after_minutes', 3);

        Cache::put(
            'test:queue-heartbeat:redis:default',
            CarbonImmutable::now('UTC')->toIso8601String(),
            now('UTC')->addMinutes(10),
        );

        $this->artisan('monitoring:queue-worker', [
            '--connection' => 'redis',
            '--queue' => 'default',
        ])->assertSuccessful();

        Notification::assertNothingSent();
    }
}
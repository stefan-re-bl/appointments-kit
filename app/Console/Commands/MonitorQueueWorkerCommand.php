<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Notifications\QueueWorkerDownNotification;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class MonitorQueueWorkerCommand extends Command
{
    protected $signature = 'monitoring:queue-worker
        {--connection= : Queue connection to check}
        {--queue= : Queue name to check}';

    protected $description = 'Check if the queue worker heartbeat is stale and send an alert if needed.';

    public function handle(): int
    {
        if (! (bool) config('monitoring.queue.enabled', true)) {
            $this->info('Queue monitoring is disabled.');

            return self::SUCCESS;
        }

        $connection = (string) ($this->option('connection') ?: config('monitoring.queue.connection', 'redis'));
        $queue = (string) ($this->option('queue') ?: config('monitoring.queue.queue', 'default'));

        $rawHeartbeat = Cache::get($this->heartbeatCacheKey($connection, $queue));

        if (! is_string($rawHeartbeat) || $rawHeartbeat === '') {
            return $this->alertWorkerDown(
                connection: $connection,
                queue: $queue,
                lastHeartbeat: null,
                reason: 'missing',
            );
        }

        try {
            $lastHeartbeat = CarbonImmutable::parse($rawHeartbeat)->utc();
        } catch (Throwable) {
            return $this->alertWorkerDown(
                connection: $connection,
                queue: $queue,
                lastHeartbeat: $rawHeartbeat,
                reason: 'invalid',
            );
        }

        $staleAfterMinutes = max(1, (int) config('monitoring.queue.stale_after_minutes', 3));
        $threshold = CarbonImmutable::now('UTC')->subMinutes($staleAfterMinutes);

        if ($lastHeartbeat->lt($threshold)) {
            return $this->alertWorkerDown(
                connection: $connection,
                queue: $queue,
                lastHeartbeat: $lastHeartbeat->toIso8601String(),
                reason: 'stale',
            );
        }

        Cache::forget($this->alertCacheKey($connection, $queue));

        $this->info("Queue worker healthy. Last heartbeat: {$lastHeartbeat->toIso8601String()}.");

        return self::SUCCESS;
    }

    private function alertWorkerDown(
        string $connection,
        string $queue,
        ?string $lastHeartbeat,
        string $reason,
    ): int {
        $staleAfterMinutes = max(1, (int) config('monitoring.queue.stale_after_minutes', 3));

        Log::critical('Queue worker heartbeat is missing or stale.', [
            'connection' => $connection,
            'queue' => $queue,
            'last_heartbeat' => $lastHeartbeat,
            'reason' => $reason,
            'stale_after_minutes' => $staleAfterMinutes,
        ]);

        $email = trim((string) config('monitoring.alerts.email', ''));

        if ($email !== '' && Cache::add(
            $this->alertCacheKey($connection, $queue),
            true,
            now('UTC')->addMinutes(max(1, (int) config('monitoring.queue.alert_cooldown_minutes', 30))),
        )) {
            Notification::route('mail', $email)->notify(
                new QueueWorkerDownNotification(
                    connectionName: $connection,
                    queueName: $queue,
                    lastHeartbeat: $lastHeartbeat,
                    staleAfterMinutes: $staleAfterMinutes,
                    reason: $reason,
                ),
            );
        }

        $this->error("Queue worker unhealthy for {$connection}:{$queue}.");

        return self::FAILURE;
    }

    private function heartbeatCacheKey(string $connection, string $queue): string
    {
        return sprintf(
            '%s:%s:%s',
            (string) config('monitoring.queue.heartbeat_cache_key', 'monitoring:queue-worker-heartbeat'),
            $connection,
            $queue,
        );
    }

    private function alertCacheKey(string $connection, string $queue): string
    {
        return sprintf(
            'monitoring:queue-worker-alert-sent:%s:%s',
            $connection,
            $queue,
        );
    }
}
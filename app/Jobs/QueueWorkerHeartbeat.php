<?php

declare(strict_types=1);

namespace App\Jobs;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class QueueWorkerHeartbeat implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 10;

    public function __construct(
        public readonly string $connectionName = 'redis',
        public readonly string $queueName = 'default',
    ) {
        //
    }

    public function handle(): void
    {
        $now = CarbonImmutable::now('UTC');

        Cache::put(
            key: $this->cacheKey(),
            value: $now->toIso8601String(),
            ttl: $now->addMinutes((int) config('monitoring.queue.heartbeat_ttl_minutes', 10)),
        );
    }

    private function cacheKey(): string
    {
        return sprintf(
            '%s:%s:%s',
            (string) config('monitoring.queue.heartbeat_cache_key', 'monitoring:queue-worker-heartbeat'),
            $this->connectionName,
            $this->queueName,
        );
    }
}
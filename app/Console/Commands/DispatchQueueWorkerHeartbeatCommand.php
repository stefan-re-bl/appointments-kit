<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\QueueWorkerHeartbeat;
use Illuminate\Console\Command;

class DispatchQueueWorkerHeartbeatCommand extends Command
{
    protected $signature = 'monitoring:queue-heartbeat';

    protected $description = 'Dispatch a queue heartbeat job to verify that the queue worker is processing jobs.';

    public function handle(): int
    {
        if (! (bool) config('monitoring.queue.enabled', true)) {
            $this->info('Queue monitoring is disabled.');

            return self::SUCCESS;
        }

        $connection = (string) config('monitoring.queue.connection', 'redis');
        $queue = (string) config('monitoring.queue.queue', 'default');

        dispatch(
            (new QueueWorkerHeartbeat(
                connectionName: $connection,
                queueName: $queue,
            ))
                ->onConnection($connection)
                ->onQueue($queue),
        );

        $this->info("Queue heartbeat dispatched to {$connection}:{$queue}.");

        return self::SUCCESS;
    }
}
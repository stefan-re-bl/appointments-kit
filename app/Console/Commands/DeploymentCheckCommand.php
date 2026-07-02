<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class DeploymentCheckCommand extends Command
{
    protected $signature = 'deployment:check
        {--url= : Public HTTPS base URL to verify}
        {--timeout=10 : HTTP timeout in seconds}
        {--profile=redis : Deployment profile to verify: redis or database}';

    protected $description = 'Verify production deployment configuration and public HTTPS endpoints.';

    /**
     * @var array<int, array{check: string, status: string, detail: string}>
     */
    private array $results = [];

    public function handle(): int
    {
        $baseUrl = $this->baseUrl();
        $timeout = max(1, (int) $this->option('timeout'));
        $profile = (string) $this->option('profile');

        $this->record('APP_ENV', app()->environment('production'), (string) config('app.env'));
        $this->record('APP_DEBUG', config('app.debug') === false, config('app.debug') ? 'true' : 'false');
        $this->record('APP_URL uses HTTPS', str_starts_with($baseUrl, 'https://'), $baseUrl);
        $this->record('Configuration cached', app()->configurationIsCached(), app()->configurationIsCached() ? 'cached' : 'not cached');
        $this->checkRuntimeProfile($profile);

        $this->checkDatabase();
        if ($profile === 'redis') {
            $this->checkRedis();
        }
        $this->checkPublicEndpoints($baseUrl, $timeout);

        $this->table(['Check', 'Status', 'Detail'], $this->results);

        return collect($this->results)->contains('status', 'FAIL')
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function baseUrl(): string
    {
        $url = (string) ($this->option('url') ?: config('app.url'));

        return rtrim($url, '/');
    }

    private function checkDatabase(): void
    {
        try {
            DB::connection()->getPdo();
            $this->record('Database connection', true, (string) config('database.default'));
        } catch (Throwable $exception) {
            $this->record('Database connection', false, $exception->getMessage());
        }
    }

    private function checkRuntimeProfile(string $profile): void
    {
        if (! in_array($profile, ['redis', 'database'], true)) {
            $this->record('Deployment profile', false, $profile);

            return;
        }

        $this->record('Deployment profile', true, $profile);

        $expected = $profile === 'redis' ? 'redis' : 'database';

        $this->record('Queue driver', config('queue.default') === $expected, (string) config('queue.default'));
        $this->record('Cache store', config('cache.default') === $expected, (string) config('cache.default'));
        $this->record('Session driver', config('session.driver') === $expected, (string) config('session.driver'));
    }

    private function checkRedis(): void
    {
        try {
            $response = Redis::connection()->ping();
            $this->record('Redis connection', true, is_string($response) ? $response : 'pong');
        } catch (Throwable $exception) {
            $this->record('Redis connection', false, $exception->getMessage());
        }
    }

    private function checkPublicEndpoints(string $baseUrl, int $timeout): void
    {
        foreach ($this->publicPaths() as $path) {
            $url = $baseUrl.$path;

            try {
                $response = Http::timeout($timeout)->get($url);

                $this->record(
                    "GET {$path}",
                    $response->ok(),
                    "HTTP {$response->status()}"
                );
            } catch (ConnectionException $exception) {
                $this->record("GET {$path}", false, $exception->getMessage());
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function publicPaths(): array
    {
        return [
            '/',
            '/how-it-works',
            '/faq',
            '/patients',
            '/payment-and-cancellation',
            '/legal',
            '/terms',
            '/privacy',
            '/emergency-notice',
            '/contact',
        ];
    }

    private function record(string $check, bool $passes, string $detail): void
    {
        $this->results[] = [
            'check' => $check,
            'status' => $passes ? 'OK' : 'FAIL',
            'detail' => $detail,
        ];
    }
}

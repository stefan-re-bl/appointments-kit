<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;

final class DeploymentCheckCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_deployment_check_passes_for_production_configuration_and_public_endpoints(): void
    {
        $this->app['env'] = 'production';

        config([
            'app.env' => 'production',
            'app.debug' => false,
            'app.url' => 'https://appointments-kit.example',
            'queue.default' => 'redis',
            'cache.default' => 'redis',
            'session.driver' => 'redis',
        ]);

        $this->fakeCachedConfiguration();

        Http::fake([
            'https://appointments-kit.example*' => Http::response('', 200),
        ]);

        Redis::shouldReceive('connection->ping')
            ->once()
            ->andReturn('PONG');

        $this
            ->artisan('deployment:check')
            ->assertSuccessful();
    }

    public function test_deployment_check_fails_when_public_url_is_not_https(): void
    {
        $this->app['env'] = 'production';

        config([
            'app.env' => 'production',
            'app.debug' => false,
            'app.url' => 'http://appointments-kit.example',
            'queue.default' => 'redis',
            'cache.default' => 'redis',
            'session.driver' => 'redis',
        ]);

        $this->fakeCachedConfiguration();

        Http::fake([
            'http://appointments-kit.example*' => Http::response('', 200),
        ]);

        Redis::shouldReceive('connection->ping')
            ->once()
            ->andReturn('PONG');

        $this
            ->artisan('deployment:check')
            ->assertFailed();
    }

    protected function tearDown(): void
    {
        $cachedConfigPath = $this->app->getCachedConfigPath();

        if (is_file($cachedConfigPath)) {
            unlink($cachedConfigPath);
        }

        Mockery::close();

        parent::tearDown();
    }

    private function fakeCachedConfiguration(): void
    {
        file_put_contents($this->app->getCachedConfigPath(), '<?php return [];');
        $this->app->instance('config_loaded_from_cache', true);
    }
}

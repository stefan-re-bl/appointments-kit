<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

final class DemoResetCommand extends Command
{
    protected $signature = 'demo:reset {--force : Allow reset in production}';

    protected $description = 'Rebuild the database and load demo data.';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to reset demo data in production without --force.');

            return self::FAILURE;
        }

        $this->call('migrate:fresh', [
            '--seed' => true,
            '--seeder' => DemoSeeder::class,
            '--force' => true,
        ]);

        $this->info('Demo environment reset with Demo Services data.');

        return self::SUCCESS;
    }
}

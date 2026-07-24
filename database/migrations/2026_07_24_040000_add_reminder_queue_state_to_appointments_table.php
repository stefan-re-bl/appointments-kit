<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table
                ->dateTime('reminder_queued_at')
                ->nullable()
                ->after('reminder_sent_at')
                ->index();
            $table
                ->dateTime('reminder_failed_at')
                ->nullable()
                ->after('reminder_queued_at')
                ->index();
            $table
                ->unsignedSmallInteger('reminder_attempts')
                ->default(0)
                ->after('reminder_failed_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropIndex(['reminder_queued_at']);
            $table->dropIndex(['reminder_failed_at']);
            $table->dropColumn([
                'reminder_queued_at',
                'reminder_failed_at',
                'reminder_attempts',
            ]);
        });
    }
};

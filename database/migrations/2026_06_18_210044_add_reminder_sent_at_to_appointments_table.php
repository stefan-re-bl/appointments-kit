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
                ->dateTime('reminder_sent_at')
                ->nullable()
                ->after('reschedule_count')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropIndex(['reminder_sent_at']);
            $table->dropColumn('reminder_sent_at');
        });
    }
};
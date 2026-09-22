<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            if (! Schema::hasColumn('appointments', 'service_id')) {
                $table->foreignId('service_id')
                    ->nullable()
                    ->after('session_type_id')
                    ->constrained('session_types')
                    ->cascadeOnDelete();
            }
        });

        DB::table('appointments')->update([
            'service_id' => DB::raw('session_type_id'),
        ]);
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            if (Schema::hasColumn('appointments', 'service_id')) {
                $table->dropConstrainedForeignId('service_id');
            }
        });
    }
};

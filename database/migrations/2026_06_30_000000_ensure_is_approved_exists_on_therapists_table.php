<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('therapists', 'is_approved')) {
            return;
        }

        Schema::table('therapists', function (Blueprint $table): void {
            $table->boolean('is_approved')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        // The original add_is_approved migration owns this column when present.
    }
};

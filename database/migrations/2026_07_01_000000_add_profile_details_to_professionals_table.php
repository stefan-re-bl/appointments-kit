<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('professionals', function (Blueprint $table): void {
            $table->text('specialties')->nullable()->after('bio');
            $table->text('therapeutic_approach')->nullable()->after('specialties');
            $table->text('payment_instructions')->nullable()->after('google_meet_link');
        });
    }

    public function down(): void
    {
        Schema::table('professionals', function (Blueprint $table): void {
            $table->dropColumn([
                'specialties',
                'therapeutic_approach',
                'payment_instructions',
            ]);
        });
    }
};

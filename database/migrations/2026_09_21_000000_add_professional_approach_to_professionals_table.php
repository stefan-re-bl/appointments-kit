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
        Schema::table('professionals', function (Blueprint $table): void {
            if (! Schema::hasColumn('professionals', 'professional_approach')) {
                $table->text('professional_approach')->nullable()->after('therapeutic_approach');
            }
        });

        if (
            Schema::hasColumn('professionals', 'professional_approach')
            && Schema::hasColumn('professionals', 'therapeutic_approach')
        ) {
            DB::table('professionals')
                ->whereNull('professional_approach')
                ->update([
                    'professional_approach' => DB::raw('therapeutic_approach'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('professionals', function (Blueprint $table): void {
            if (Schema::hasColumn('professionals', 'professional_approach')) {
                $table->dropColumn('professional_approach');
            }
        });
    }
};

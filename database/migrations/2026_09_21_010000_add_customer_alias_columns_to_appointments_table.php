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
            if (! Schema::hasColumn('appointments', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('patient_name');
            }

            if (! Schema::hasColumn('appointments', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('patient_email');
            }

            if (! Schema::hasColumn('appointments', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('patient_phone');
            }

            if (! Schema::hasColumn('appointments', 'customer_timezone')) {
                $table->string('customer_timezone')->nullable()->after('patient_timezone');
            }

            if (! Schema::hasColumn('appointments', 'customer_locale')) {
                $table->string('customer_locale', 2)->nullable()->after('patient_locale');
            }

            if (! Schema::hasColumn('appointments', 'customer_whatsapp_opt_in_at')) {
                $table->timestamp('customer_whatsapp_opt_in_at')->nullable()->after('patient_whatsapp_opt_in_at');
            }

            if (! Schema::hasColumn('appointments', 'customer_whatsapp_opt_out_at')) {
                $table->timestamp('customer_whatsapp_opt_out_at')->nullable()->after('patient_whatsapp_opt_out_at');
            }
        });

        DB::table('appointments')->update([
            'customer_name' => DB::raw('patient_name'),
            'customer_email' => DB::raw('patient_email'),
            'customer_phone' => DB::raw('patient_phone'),
            'customer_timezone' => DB::raw('patient_timezone'),
            'customer_locale' => DB::raw('patient_locale'),
            'customer_whatsapp_opt_in_at' => DB::raw('patient_whatsapp_opt_in_at'),
            'customer_whatsapp_opt_out_at' => DB::raw('patient_whatsapp_opt_out_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropColumn([
                'customer_name',
                'customer_email',
                'customer_phone',
                'customer_timezone',
                'customer_locale',
                'customer_whatsapp_opt_in_at',
                'customer_whatsapp_opt_out_at',
            ]);
        });
    }
};

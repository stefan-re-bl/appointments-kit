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
            $table->string('patient_phone')->nullable()->after('patient_email');
            $table->string('patient_locale', 2)->default('es')->after('patient_timezone');
            $table->timestamp('patient_whatsapp_opt_in_at')->nullable()->after('terms_accepted_at');
            $table->timestamp('patient_whatsapp_opt_out_at')->nullable()->after('patient_whatsapp_opt_in_at');
        });

        Schema::table('therapists', function (Blueprint $table): void {
            $table->string('whatsapp_phone')->nullable()->after('google_meet_link');
            $table->boolean('whatsapp_notifications_enabled')->default(false)->after('whatsapp_phone');
            $table->boolean('whatsapp_confirmations_enabled')->default(true)->after('whatsapp_notifications_enabled');
            $table->boolean('whatsapp_reminders_enabled')->default(true)->after('whatsapp_confirmations_enabled');
            $table->string('preferred_locale', 2)->default('es')->after('whatsapp_reminders_enabled');
        });

        Schema::create('notification_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->string('channel');
            $table->string('recipient_type');
            $table->string('recipient_address');
            $table->string('recipient_locale', 2);
            $table->string('provider');
            $table->string('provider_message_id')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('event_version')->default(0);
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('last_error_code')->nullable();
            $table->string('last_error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(
                ['appointment_id', 'event', 'channel', 'recipient_type', 'event_version'],
                'notification_deliveries_idempotency_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');

        Schema::table('therapists', function (Blueprint $table): void {
            $table->dropColumn([
                'whatsapp_phone',
                'whatsapp_notifications_enabled',
                'whatsapp_confirmations_enabled',
                'whatsapp_reminders_enabled',
                'preferred_locale',
            ]);
        });

        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropColumn([
                'patient_phone',
                'patient_locale',
                'patient_whatsapp_opt_in_at',
                'patient_whatsapp_opt_out_at',
            ]);
        });
    }
};

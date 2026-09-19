<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_type_id')->constrained()->cascadeOnDelete();

            // Datos del paciente (sin login)
            $table->string('patient_name');
            $table->string('patient_email');
            $table->string('patient_timezone')->default('UTC');

            // Horarios en UTC (Estricto según regla de oro)
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            // Estado y pagos
            $table->string('status')->default('pending')->index();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3);
            $table->string('payment_transaction_id')->nullable()->unique();
            $table->timestamp('payment_processed_at')->nullable();

            // Utilidades
            $table->string('token', 64)->unique(); // Para URL pública /appointment/{token}
            $table->unsignedInteger('reschedule_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

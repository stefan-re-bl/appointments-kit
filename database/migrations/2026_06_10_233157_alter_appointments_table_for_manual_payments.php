<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // 1. Eliminar campos de pasarelas de pago (webhooks)
            $table->dropUnique('appointments_payment_transaction_id_unique');
            $table->dropColumn(['payment_transaction_id', 'payment_processed_at']);

            // 2. Añadir campos de seguimiento manual de pagos
            $table->string('payment_status')->default('pending')->after('currency');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Rollback: restaurar campos viejos y borrar nuevos
            $table->string('payment_transaction_id')->nullable()->unique()->after('currency');
            $table->timestamp('payment_processed_at')->nullable()->after('payment_transaction_id');

            $table->dropColumn(['payment_status', 'paid_at']);
        });
    }
};

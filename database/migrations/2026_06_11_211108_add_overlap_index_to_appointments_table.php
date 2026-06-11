<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Índice compuesto CRUCIAL para la consulta de overlapping y bloqueo pesimista.
            // Asegura que MySQL pueda buscar y bloquear los rangos horarios de un terapeuta
            // sin hacer un escaneo completo de la tabla (Full Table Scan).
            $table->index(['therapist_id', 'starts_at', 'ends_at'], 'appointments_overlap_index');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_overlap_index');
        });
    }
};
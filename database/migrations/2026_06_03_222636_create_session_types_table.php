<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_id')->constrained('professionals')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('duration_minutes');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('ARS');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Restricción: no se permite duplicar nombre para el mismo profesional.
            $table->unique(['professional_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_types');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('therapist_id')->constrained()->cascadeOnDelete();
            // 1 = Lunes, 7 = Domingo (Estándar ISO-8601)
            $table->tinyInteger('day_of_week'); 
            // Se almacenarán en UTC
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
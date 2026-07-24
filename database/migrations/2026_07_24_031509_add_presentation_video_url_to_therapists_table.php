<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('therapists', function (Blueprint $table): void {
            $table->string('presentation_video_url')->nullable()->after('avatar_url');
        });
    }

    public function down(): void
    {
        Schema::table('therapists', function (Blueprint $table): void {
            $table->dropColumn('presentation_video_url');
        });
    }
};

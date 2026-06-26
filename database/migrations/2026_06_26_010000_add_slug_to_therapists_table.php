<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('therapists', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique()->after('user_id');
        });

        $therapists = DB::table('therapists')
            ->join('users', 'therapists.user_id', '=', 'users.id')
            ->select('therapists.id', 'users.name')
            ->orderBy('therapists.id')
            ->get();

        $usedSlugs = [];

        foreach ($therapists as $therapist) {
            $baseSlug = Str::slug((string) $therapist->name) ?: 'therapist';
            $slug = $baseSlug;
            $counter = 2;

            while (in_array($slug, $usedSlugs, true)) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $usedSlugs[] = $slug;

            DB::table('therapists')
                ->where('id', $therapist->id)
                ->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('therapists', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};

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
        Schema::table('professionals', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique()->after('user_id');
        });

        $professionals = DB::table('professionals')
            ->join('users', 'professionals.user_id', '=', 'users.id')
            ->select('professionals.id', 'users.name')
            ->orderBy('professionals.id')
            ->get();

        $usedSlugs = [];

        foreach ($professionals as $professional) {
            $baseSlug = Str::slug((string) $professional->name) ?: 'professional';
            $slug = $baseSlug;
            $counter = 2;

            while (in_array($slug, $usedSlugs, true)) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $usedSlugs[] = $slug;

            DB::table('professionals')
                ->where('id', $professional->id)
                ->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('professionals', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};

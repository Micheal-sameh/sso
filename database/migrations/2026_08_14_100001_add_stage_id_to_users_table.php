<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('stage_id')->nullable()->after('role')->constrained('stages')->restrictOnDelete();
        });

        // Guarantee a fallback stage exists to backfill any existing users,
        // independent of whether StageSeeder has run yet.
        $fallbackStageId = DB::table('stages')->where('code', 'adult')->value('id');

        if ($fallbackStageId === null) {
            $fallbackStageId = DB::table('stages')->insertGetId([
                'name' => 'Adult',
                'code' => 'adult',
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('users')->whereNull('stage_id')->update(['stage_id' => $fallbackStageId]);

        DB::statement('ALTER TABLE users MODIFY COLUMN stage_id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['stage_id']);
            $table->dropColumn('stage_id');
        });
    }
};

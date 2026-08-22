<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type')->default('manual')->after('target_type');
        });

        // Backfill existing rows as best-effort: any notification tied to a CMC
        // post whose post came from a synced YouTube video is "youtube",
        // otherwise "cmc"; everything else stays the default "manual".
        DB::table('notifications')
            ->whereNotNull('cmc_post_id')
            ->whereNotIn('cmc_post_id', DB::table('youtube_videos')->whereNotNull('cmc_post_id')->pluck('cmc_post_id'))
            ->update(['type' => 'cmc']);

        DB::table('notifications')
            ->whereNotNull('cmc_post_id')
            ->whereIn('cmc_post_id', DB::table('youtube_videos')->whereNotNull('cmc_post_id')->pluck('cmc_post_id'))
            ->update(['type' => 'youtube']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};

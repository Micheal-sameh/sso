<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cmc_posts', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false)->index()->after('created_by');
            $table->unsignedInteger('pin_order')->nullable()->after('is_pinned');
        });
    }

    public function down(): void
    {
        Schema::table('cmc_posts', function (Blueprint $table) {
            $table->dropColumn(['is_pinned', 'pin_order']);
        });
    }
};

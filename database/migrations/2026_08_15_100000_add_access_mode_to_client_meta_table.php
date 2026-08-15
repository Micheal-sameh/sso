<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_meta', function (Blueprint $table) {
            $table->enum('access_mode', ['all', 'stage', 'specific'])->default('all')->after('is_verified');
        });
    }

    public function down(): void
    {
        Schema::table('client_meta', function (Blueprint $table) {
            $table->dropColumn('access_mode');
        });
    }
};

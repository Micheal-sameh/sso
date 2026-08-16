<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_meta', function (Blueprint $table) {
            $table->dropColumn('app_logo_url');
        });
    }

    public function down(): void
    {
        Schema::table('client_meta', function (Blueprint $table) {
            $table->string('app_logo_url')->nullable()->after('app_name');
        });
    }
};

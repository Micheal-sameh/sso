<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_meta', function (Blueprint $table) {
            $table->string('logout_uri')->nullable()->after('homepage_url');
            $table->text('logout_secret')->nullable()->after('logout_uri');
        });
    }

    public function down(): void
    {
        Schema::table('client_meta', function (Blueprint $table) {
            $table->dropColumn(['logout_uri', 'logout_secret']);
        });
    }
};

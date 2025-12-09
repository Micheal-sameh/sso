<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sso_id')->unique()->nullable()->after('id');
            $table->boolean('mfa_enabled')->default(false)->after('password');
            $table->string('mfa_secret')->nullable()->after('mfa_enabled');
            $table->timestamp('last_login_at')->nullable()->after('mfa_secret');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sso_id', 'mfa_enabled', 'mfa_secret', 'last_login_at', 'last_login_ip']);
        });
    }
};

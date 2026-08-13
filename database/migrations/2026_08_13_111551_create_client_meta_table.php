<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_meta', function (Blueprint $table) {
            $table->id();
            $table->uuid('oauth_client_id');
            $table->foreign('oauth_client_id')->references('id')->on('oauth_clients')->cascadeOnDelete();
            $table->string('app_name');
            $table->string('app_logo_url')->nullable();
            $table->string('homepage_url')->nullable();
            $table->boolean('is_internal')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_meta');
    }
};

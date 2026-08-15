<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_stage_access', function (Blueprint $table) {
            $table->id();
            $table->uuid('oauth_client_id');
            $table->foreign('oauth_client_id')->references('id')->on('oauth_clients')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('stages')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['oauth_client_id', 'stage_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_stage_access');
    }
};

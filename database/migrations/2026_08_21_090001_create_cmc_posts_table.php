<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cmc_posts', function (Blueprint $table) {
            $table->id();
            $table->text('text')->nullable();
            $table->string('url')->nullable();
            $table->string('url_title')->nullable();
            $table->text('url_description')->nullable();
            $table->string('url_image')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cmc_posts');
    }
};

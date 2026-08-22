<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('donation_plan_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('gateway');
            // initiate | callback | recharge
            $table->string('type');
            $table->string('status');
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_transactions');
    }
};

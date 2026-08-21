<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('fund_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EGP');
            $table->string('gateway');
            $table->string('frequency')->default('monthly');
            // Populated once the first payment on the plan succeeds and the
            // bank hands back a reusable charge token; null beforehand.
            $table->string('gateway_token')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('next_charge_at')->nullable();
            $table->timestamp('last_charged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_plans');
    }
};

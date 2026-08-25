<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spiritual_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spiritual_note_label_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->boolean('is_checked')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'spiritual_note_label_id', 'date']);
            $table->index(['user_id', 'date']);
            $table->index(['spiritual_note_label_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spiritual_notes');
    }
};

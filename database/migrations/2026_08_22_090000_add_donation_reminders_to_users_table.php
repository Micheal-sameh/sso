<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('wants_donation_reminders')->default(false)->after('requires_data_update');
            // Day-of-month captured when the user opts in; the monthly nudge fires on
            // this day each month, clamped to the shorter month (e.g. day 31 -> Feb 28/29).
            $table->unsignedTinyInteger('donation_reminder_day')->nullable()->after('wants_donation_reminders');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['wants_donation_reminders', 'donation_reminder_day']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE client_meta MODIFY access_mode ENUM('all', 'stage', 'specific', 'group') NOT NULL DEFAULT 'all'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE client_meta MODIFY access_mode ENUM('all', 'stage', 'specific') NOT NULL DEFAULT 'all'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * OAuth clients allowed to call POST /api/external/users/sync. Valid client
     * credentials alone are not enough; the client must be listed here.
     */
    public function up(): void
    {
        Schema::create('external_sync_clients', function (Blueprint $table) {
            $table->id();
            $table->uuid('oauth_client_id')->unique();
            $table->foreign('oauth_client_id')->references('id')->on('oauth_clients')->cascadeOnDelete();
            $table->timestamps();
        });

        // Allow the existing "SSO Sync (SSRS)" client so the current relay keeps working.
        $existing = DB::table('oauth_clients')->where('name', 'SSO Sync (SSRS)')->pluck('id');

        foreach ($existing as $id) {
            DB::table('external_sync_clients')->insert([
                'oauth_client_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('external_sync_clients');
    }
};

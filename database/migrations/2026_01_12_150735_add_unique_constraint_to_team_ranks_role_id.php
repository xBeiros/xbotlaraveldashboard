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
        Schema::table('team_ranks', function (Blueprint $table) {
            // Füge Unique-Constraint hinzu: role_id muss pro guild_id eindeutig sein
            $table->unique(['guild_id', 'role_id'], 'team_ranks_guild_role_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_ranks', function (Blueprint $table) {
            $table->dropUnique('team_ranks_guild_role_unique');
        });
    }
};

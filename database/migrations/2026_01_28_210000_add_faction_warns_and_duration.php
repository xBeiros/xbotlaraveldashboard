<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faction_warns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faction_id')->constrained('factions')->onDelete('cascade');
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->text('reason')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['guild_id', 'expires_at']);
        });

        Schema::table('faction_management_configs', function (Blueprint $table) {
            $table->unsignedInteger('warn_duration_days')->nullable()->after('embed_overview');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faction_warns');
        Schema::table('faction_management_configs', function (Blueprint $table) {
            $table->dropColumn('warn_duration_days');
        });
    }
};

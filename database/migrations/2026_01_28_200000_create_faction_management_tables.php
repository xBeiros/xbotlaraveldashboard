<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();

            $table->unique(['guild_id', 'name']);
        });

        Schema::create('faction_management_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->string('channel_id_create')->nullable();
            $table->string('channel_id_warn')->nullable();
            $table->string('channel_id_dissolve')->nullable();
            $table->string('channel_id_overview')->nullable();
            $table->string('overview_message_id')->nullable();
            $table->json('embed_create')->nullable();
            $table->json('embed_warn')->nullable();
            $table->json('embed_dissolve')->nullable();
            $table->json('embed_overview')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faction_management_configs');
        Schema::dropIfExists('factions');
    }
};

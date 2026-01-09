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
        Schema::create('team_ranks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->string('name'); // z.B. "Owner", "Admin", "Moderator"
            $table->integer('sort_order')->default(0);
            $table->boolean('visible')->default(true);
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->foreignId('rank_id')->constrained('team_ranks')->onDelete('cascade');
            $table->string('user_id'); // Discord User ID
            $table->timestamps();

            $table->unique(['guild_id', 'user_id']);
        });

        Schema::create('team_management_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->string('channel_id')->nullable();
            $table->boolean('notify_join')->default(true);
            $table->boolean('notify_leave')->default(true);
            $table->boolean('notify_upgrade')->default(true);
            $table->boolean('notify_downgrade')->default(true);
            $table->json('join_embed')->nullable(); // Embed-Konfiguration
            $table->json('leave_embed')->nullable();
            $table->json('upgrade_embed')->nullable();
            $table->json('downgrade_embed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_management_configs');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('team_ranks');
    }
};

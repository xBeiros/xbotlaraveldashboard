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
        Schema::create('auto_moderation_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            
            // Bad Words
            $table->boolean('bad_words_enabled')->default(false);
            $table->text('bad_words_list')->nullable(); // JSON array of words
            $table->boolean('bad_words_delete_message')->default(true);
            $table->text('bad_words_warning_message')->nullable();
            
            // Discord Invite Links
            $table->boolean('block_discord_invites')->default(false);
            $table->boolean('block_discord_invites_delete_message')->default(true);
            $table->text('block_discord_invites_warning_message')->nullable();
            
            // Whitelists
            $table->text('whitelist_channels')->nullable(); // JSON array of channel IDs
            $table->text('whitelist_roles')->nullable(); // JSON array of role IDs
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_moderation_configs');
    }
};

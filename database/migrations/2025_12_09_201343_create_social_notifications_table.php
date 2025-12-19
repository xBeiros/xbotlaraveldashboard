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
        if (!Schema::hasTable('social_notifications') && Schema::hasTable('guilds')) {
            Schema::create('social_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->string('platform'); // twitch, tiktok, x, bluesky, youtube, reddit, instagram, rss, kick, podcast
            $table->string('channel_id')->nullable(); // Discord Channel ID
            $table->string('username')->nullable(); // Username/Channel Name der Plattform
            $table->string('webhook_url')->nullable(); // Für RSS/Webhooks
            $table->boolean('enabled')->default(false);
            $table->boolean('notify_live')->default(true); // Benachrichtigung wenn Live
            $table->text('custom_message')->nullable(); // Custom Nachricht
            $table->timestamps();
            
            $table->unique(['guild_id', 'platform', 'username']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_notifications');
    }
};

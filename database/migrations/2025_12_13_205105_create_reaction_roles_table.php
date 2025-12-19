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
        if (!Schema::hasTable('reaction_roles') && Schema::hasTable('guilds')) {
            Schema::create('reaction_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->string('message_id')->nullable(); // Discord message ID
            $table->string('channel_id'); // Discord channel ID
            $table->boolean('enabled')->default(true);
            
            // Embed configuration
            $table->string('embed_title')->nullable();
            $table->text('embed_description')->nullable();
            $table->string('embed_color')->default('#5865f2');
            $table->string('embed_thumbnail')->nullable();
            $table->string('embed_image')->nullable();
            $table->string('embed_banner')->nullable(); // Banner image URL
            $table->boolean('embed_footer')->default(true);
            
            // Reaction roles data (JSON: [{"emoji": "👍", "role_id": "123456789", "label": "Member"}])
            $table->json('reactions');
            
            $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reaction_roles');
    }
};

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
        Schema::table('auto_moderation_configs', function (Blueprint $table) {
            // Bad Words Embed
            $table->boolean('bad_words_use_embed')->default(false)->after('bad_words_warning_message');
            $table->string('bad_words_embed_title')->nullable()->after('bad_words_use_embed');
            $table->text('bad_words_embed_description')->nullable()->after('bad_words_embed_title');
            $table->string('bad_words_embed_color')->default('#ff0000')->after('bad_words_embed_description');
            $table->boolean('bad_words_embed_footer')->default(true)->after('bad_words_embed_color');
            
            // Discord Invites Embed
            $table->boolean('block_discord_invites_use_embed')->default(false)->after('block_discord_invites_warning_message');
            $table->string('block_discord_invites_embed_title')->nullable()->after('block_discord_invites_use_embed');
            $table->text('block_discord_invites_embed_description')->nullable()->after('block_discord_invites_embed_title');
            $table->string('block_discord_invites_embed_color')->default('#ff0000')->after('block_discord_invites_embed_description');
            $table->boolean('block_discord_invites_embed_footer')->default(true)->after('block_discord_invites_embed_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_moderation_configs', function (Blueprint $table) {
            $table->dropColumn([
                'bad_words_use_embed',
                'bad_words_embed_title',
                'bad_words_embed_description',
                'bad_words_embed_color',
                'bad_words_embed_footer',
                'block_discord_invites_use_embed',
                'block_discord_invites_embed_title',
                'block_discord_invites_embed_description',
                'block_discord_invites_embed_color',
                'block_discord_invites_embed_footer',
            ]);
        });
    }
};

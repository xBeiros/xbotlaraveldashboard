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
        Schema::table('goodbye_configs', function (Blueprint $table) {
            // Embed-Felder
            $table->boolean('use_embed')->default(false)->after('enabled');
            $table->string('embed_title')->nullable()->after('use_embed');
            $table->text('embed_description')->nullable()->after('embed_title');
            $table->string('embed_color')->nullable()->after('embed_description');
            $table->string('embed_thumbnail')->nullable()->after('embed_color');
            $table->string('embed_image')->nullable()->after('embed_thumbnail');
            $table->boolean('embed_footer')->default(true)->after('embed_image');
            
            // Card-Felder
            $table->boolean('use_goodbye_card')->default(false)->after('embed_footer');
            $table->string('card_font')->nullable()->after('use_goodbye_card');
            $table->string('card_text_color')->nullable()->after('card_font');
            $table->string('card_background_color')->nullable()->after('card_text_color');
            $table->integer('card_overlay_opacity')->default(50)->after('card_background_color');
            $table->longText('card_background_image')->nullable()->after('card_overlay_opacity');
            $table->string('card_title')->nullable()->after('card_background_image');
            $table->string('card_avatar_position')->default('top')->after('card_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goodbye_configs', function (Blueprint $table) {
            $table->dropColumn([
                'use_embed',
                'embed_title',
                'embed_description',
                'embed_color',
                'embed_thumbnail',
                'embed_image',
                'embed_footer',
                'use_goodbye_card',
                'card_font',
                'card_text_color',
                'card_background_color',
                'card_overlay_opacity',
                'card_background_image',
                'card_title',
                'card_avatar_position',
            ]);
        });
    }
};

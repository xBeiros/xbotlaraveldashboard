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
        if (Schema::hasTable('welcome_configs')) {
            Schema::table('welcome_configs', function (Blueprint $table) {
            $table->boolean('use_welcome_card')->default(false)->after('embed_footer');
            $table->string('card_font')->nullable()->after('use_welcome_card');
            $table->string('card_text_color')->nullable()->after('card_font');
            $table->string('card_background_color')->nullable()->after('card_text_color');
            $table->integer('card_overlay_opacity')->default(50)->after('card_background_color');
            $table->string('card_background_image')->nullable()->after('card_overlay_opacity');
            $table->string('card_title')->nullable()->after('card_background_image');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('welcome_configs')) {
            Schema::table('welcome_configs', function (Blueprint $table) {
            $table->dropColumn([
                'use_welcome_card',
                'card_font',
                'card_text_color',
                'card_background_color',
                'card_overlay_opacity',
                'card_background_image',
                'card_title',
            ]);
            });
        }
    }
};

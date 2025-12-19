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
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->boolean('use_welcome_card')->default(false)->after('welcome_message');
            $table->string('card_background_type')->default('color')->after('use_welcome_card');
            $table->string('card_background_color')->nullable()->after('card_background_type');
            $table->text('card_background_image')->nullable()->after('card_background_color');
            $table->integer('card_overlay_opacity')->default(0)->after('card_background_image');
            $table->string('card_text_color')->default('#ffffff')->after('card_overlay_opacity');
            $table->string('card_font')->default('Arial')->after('card_text_color');
            $table->string('card_avatar_position')->default('left')->after('card_font');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->dropColumn([
                'use_welcome_card',
                'card_background_type',
                'card_background_color',
                'card_background_image',
                'card_overlay_opacity',
                'card_text_color',
                'card_font',
                'card_avatar_position',
            ]);
        });
    }
};


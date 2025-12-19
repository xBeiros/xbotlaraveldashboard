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
        if (Schema::hasTable('ticket_categories')) {
            Schema::table('ticket_categories', function (Blueprint $table) {
                if (!Schema::hasColumn('ticket_categories', 'use_welcome_card')) {
                    $table->boolean('use_welcome_card')->default(false)->after('welcome_message');
                }
                if (!Schema::hasColumn('ticket_categories', 'card_background_type')) {
                    $table->string('card_background_type')->default('color')->after('use_welcome_card');
                }
                if (!Schema::hasColumn('ticket_categories', 'card_background_color')) {
                    $table->string('card_background_color')->nullable()->after('card_background_type');
                }
                if (!Schema::hasColumn('ticket_categories', 'card_background_image')) {
                    $table->text('card_background_image')->nullable()->after('card_background_color');
                }
                if (!Schema::hasColumn('ticket_categories', 'card_overlay_opacity')) {
                    $table->integer('card_overlay_opacity')->default(0)->after('card_background_image');
                }
                if (!Schema::hasColumn('ticket_categories', 'card_text_color')) {
                    $table->string('card_text_color')->default('#ffffff')->after('card_overlay_opacity');
                }
                if (!Schema::hasColumn('ticket_categories', 'card_font')) {
                    $table->string('card_font')->default('Arial')->after('card_text_color');
                }
                if (!Schema::hasColumn('ticket_categories', 'card_avatar_position')) {
                    $table->string('card_avatar_position')->default('left')->after('card_font');
                }
            });
        }
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


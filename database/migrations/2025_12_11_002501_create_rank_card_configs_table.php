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
        Schema::create('rank_card_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            
            // Hintergrund
            $table->string('background_type')->default('color'); // 'color', 'image', 'custom'
            $table->string('background_color')->default('#000000');
            $table->string('background_image')->nullable(); // Name des Preset-Bildes
            $table->text('custom_background_url')->nullable(); // URL für benutzerdefinierten Hintergrund
            
            // Überlagerung
            $table->integer('overlay_opacity')->default(0); // 0-100
            
            // Farben
            $table->string('text_color')->default('#ffffff');
            $table->string('rank_text_color')->default('#ffffff');
            $table->string('level_text_color')->default('#5865f2');
            $table->string('xp_text_color')->default('#ffffff');
            $table->string('progress_bar_color')->default('#5865f2');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rank_card_configs');
    }
};

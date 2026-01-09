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
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('guild_id'); // Discord Guild ID (nullable für globale Widgets)
            $table->string('widget_type'); // z.B. 'members', 'tickets', 'giveaways', 'leveling'
            $table->integer('position')->default(0); // Position im Grid
            $table->integer('column')->default(0); // Spalte im Grid
            $table->integer('row')->default(0); // Zeile im Grid
            $table->json('config')->nullable(); // Widget-spezifische Konfiguration
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            
            // Index für schnelle Abfragen
            $table->index(['user_id', 'guild_id']);
            $table->index(['user_id', 'guild_id', 'enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};

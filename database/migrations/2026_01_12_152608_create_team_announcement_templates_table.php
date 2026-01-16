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
        Schema::create('team_announcement_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->string('name'); // z.B. "Team Aufnahme", "Kick", "Upgrade", "Downgrade"
            $table->string('type')->default('custom'); // 'join', 'leave', 'upgrade', 'downgrade', 'custom'
            $table->json('embed')->nullable(); // Embed-Konfiguration (title, description, color, etc.)
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_announcement_templates');
    }
};

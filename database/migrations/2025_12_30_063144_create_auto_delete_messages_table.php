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
        Schema::create('auto_delete_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->string('channel_id'); // Discord Channel-ID
            $table->integer('interval_minutes'); // Intervall in Minuten (5, 10, 30, 60)
            $table->integer('delete_count')->default(100); // Anzahl der zu löschenden Nachrichten (max 100)
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
            
            // Eindeutige Kombination: Ein Channel kann nur eine automatische Löschung haben
            $table->unique(['guild_id', 'channel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_delete_messages');
    }
};

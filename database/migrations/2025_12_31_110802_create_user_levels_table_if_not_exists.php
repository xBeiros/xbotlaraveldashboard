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
        if (!Schema::hasTable('user_levels')) {
            Schema::create('user_levels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
                $table->string('user_id'); // Discord User ID
                $table->integer('xp')->default(0);
                $table->integer('level')->default(1);
                $table->timestamps();
                
                $table->unique(['guild_id', 'user_id']);
                $table->index(['guild_id', 'level', 'xp']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_levels');
    }
};

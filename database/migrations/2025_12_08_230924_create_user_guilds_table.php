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
        if (!Schema::hasTable('user_guilds') && Schema::hasTable('users')) {
            Schema::create('user_guilds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('guild_id'); // Nicht unique, da mehrere User Zugriff auf denselben Server haben können
            $table->string('name');
            $table->string('icon')->nullable();
            $table->boolean('owner')->default(false);
            $table->bigInteger('permissions')->default(0);
            $table->boolean('bot_joined')->default(false);
            $table->timestamps();
            
            // Composite Unique Key: Ein User kann nicht zweimal denselben Server haben
            $table->unique(['user_id', 'guild_id'], 'user_guilds_user_guild_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_guilds');
    }
};

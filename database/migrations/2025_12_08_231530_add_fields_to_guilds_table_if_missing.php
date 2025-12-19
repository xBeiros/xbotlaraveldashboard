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
        // Erstelle die Tabelle, falls sie nicht existiert
        if (!Schema::hasTable('guilds')) {
            Schema::create('guilds', function (Blueprint $table) {
                $table->id();
                $table->string('discord_id')->unique();
                $table->string('name');
                $table->string('icon')->nullable();
                $table->string('owner_id');
                $table->boolean('bot_active')->default(true);
                $table->string('prefix')->default('!');
                $table->timestamps();
            });
        } else {
            // Füge fehlende Spalten hinzu, falls die Tabelle bereits existiert
            Schema::table('guilds', function (Blueprint $table) {
                if (!Schema::hasColumn('guilds', 'discord_id')) {
                    $table->string('discord_id')->unique()->after('id');
                }
                if (!Schema::hasColumn('guilds', 'name')) {
                    $table->string('name')->after('discord_id');
                }
                if (!Schema::hasColumn('guilds', 'icon')) {
                    $table->string('icon')->nullable()->after('name');
                }
                if (!Schema::hasColumn('guilds', 'owner_id')) {
                    $table->string('owner_id')->after('icon');
                }
                if (!Schema::hasColumn('guilds', 'bot_active')) {
                    $table->boolean('bot_active')->default(true)->after('owner_id');
                }
                if (!Schema::hasColumn('guilds', 'prefix')) {
                    $table->string('prefix')->default('!')->after('bot_active');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('guilds')) {
            Schema::dropIfExists('guilds');
        }
    }
};

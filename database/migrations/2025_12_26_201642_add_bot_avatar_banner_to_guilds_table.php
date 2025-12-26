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
        Schema::table('guilds', function (Blueprint $table) {
            if (!Schema::hasColumn('guilds', 'bot_avatar')) {
                $table->string('bot_avatar')->nullable()->after('ticket_transcript_enabled');
            }
            if (!Schema::hasColumn('guilds', 'bot_banner')) {
                $table->string('bot_banner')->nullable()->after('bot_avatar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guilds', function (Blueprint $table) {
            if (Schema::hasColumn('guilds', 'bot_avatar')) {
                $table->dropColumn('bot_avatar');
            }
            if (Schema::hasColumn('guilds', 'bot_banner')) {
                $table->dropColumn('bot_banner');
            }
        });
    }
};

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
            if (!Schema::hasColumn('guilds', 'bot_nickname')) {
                $table->string('bot_nickname')->nullable()->after('bot_banner');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guilds', function (Blueprint $table) {
            if (Schema::hasColumn('guilds', 'bot_nickname')) {
                $table->dropColumn('bot_nickname');
            }
        });
    }
};

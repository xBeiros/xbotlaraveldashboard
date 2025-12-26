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
            if (!Schema::hasColumn('guilds', 'ticket_transcript_enabled')) {
                $table->boolean('ticket_transcript_enabled')->default(true)->after('language');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guilds', function (Blueprint $table) {
            if (Schema::hasColumn('guilds', 'ticket_transcript_enabled')) {
                $table->dropColumn('ticket_transcript_enabled');
            }
        });
    }
};

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
            $table->boolean('ticket_close_require_confirmation')->default(false)->after('ticket_transcript_enabled');
            $table->text('ticket_close_message')->nullable()->after('ticket_close_require_confirmation');
            $table->string('ticket_close_confirmation_button_text')->nullable()->after('ticket_close_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guilds', function (Blueprint $table) {
            $table->dropColumn([
                'ticket_close_require_confirmation',
                'ticket_close_message',
                'ticket_close_confirmation_button_text',
            ]);
        });
    }
};

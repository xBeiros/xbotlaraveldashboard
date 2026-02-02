<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guild_statistics_config', function (Blueprint $table) {
            $table->timestamp('force_update_requested_at')->nullable()->after('last_message_edit_at');
        });
    }

    public function down(): void
    {
        Schema::table('guild_statistics_config', function (Blueprint $table) {
            $table->dropColumn('force_update_requested_at');
        });
    }
};

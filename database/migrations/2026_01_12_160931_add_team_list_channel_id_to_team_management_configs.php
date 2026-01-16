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
        Schema::table('team_management_configs', function (Blueprint $table) {
            $table->string('team_list_channel_id')->nullable()->after('channel_id');
            $table->string('team_list_message_id')->nullable()->after('team_list_channel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_management_configs', function (Blueprint $table) {
            $table->dropColumn(['team_list_channel_id', 'team_list_message_id']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guild_statistics_config', function (Blueprint $table) {
            $table->string('channel_id_members', 32)->nullable()->after('message_id');
            $table->string('channel_id_joins', 32)->nullable()->after('channel_id_members');
            $table->string('channel_id_leaves', 32)->nullable()->after('channel_id_joins');
            $table->string('channel_id_vc', 32)->nullable()->after('channel_id_leaves');
            $table->string('channel_id_boosts', 32)->nullable()->after('channel_id_vc');
            $table->string('channel_name_members', 100)->nullable()->after('channel_name');
            $table->string('channel_name_joins', 100)->nullable()->after('channel_name_members');
            $table->string('channel_name_leaves', 100)->nullable()->after('channel_name_joins');
            $table->string('channel_name_vc', 100)->nullable()->after('channel_name_leaves');
            $table->string('channel_name_boosts', 100)->nullable()->after('channel_name_vc');
        });
    }

    public function down(): void
    {
        Schema::table('guild_statistics_config', function (Blueprint $table) {
            $table->dropColumn([
                'channel_id_members', 'channel_id_joins', 'channel_id_leaves', 'channel_id_vc', 'channel_id_boosts',
                'channel_name_members', 'channel_name_joins', 'channel_name_leaves', 'channel_name_vc', 'channel_name_boosts',
            ]);
        });
    }
};

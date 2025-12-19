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
        Schema::table('leveling_configs', function (Blueprint $table) {
            // Level-Up Ankündigung
            if (!Schema::hasColumn('leveling_configs', 'level_up_type')) {
                $table->string('level_up_type')->default('current_channel')->after('level_up_channel_id');
            }
            
            // Rollenbelohnungen
            if (!Schema::hasColumn('leveling_configs', 'role_reward_type')) {
                $table->string('role_reward_type')->default('stack')->after('level_up_message');
            }
            if (!Schema::hasColumn('leveling_configs', 'remove_role_on_xp_loss')) {
                $table->boolean('remove_role_on_xp_loss')->default(false)->after('role_reward_type');
            }
            
            // Von XP ausgeschlossene Rollen
            if (!Schema::hasColumn('leveling_configs', 'excluded_roles_type')) {
                $table->string('excluded_roles_type')->default('allow_all_except')->after('remove_role_on_xp_loss');
            }
            if (!Schema::hasColumn('leveling_configs', 'excluded_roles')) {
                $table->text('excluded_roles')->nullable()->after('excluded_roles_type');
            }
            
            // Von XP ausgeschlossene Kanäle
            if (!Schema::hasColumn('leveling_configs', 'excluded_channels_type')) {
                $table->string('excluded_channels_type')->default('allow_all_except')->after('excluded_roles');
            }
            if (!Schema::hasColumn('leveling_configs', 'excluded_channels')) {
                $table->text('excluded_channels')->nullable()->after('excluded_channels_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leveling_configs', function (Blueprint $table) {
            $table->dropColumn([
                'level_up_type',
                'role_reward_type',
                'remove_role_on_xp_loss',
                'excluded_roles_type',
                'excluded_roles',
                'excluded_channels_type',
                'excluded_channels',
            ]);
        });
    }
};


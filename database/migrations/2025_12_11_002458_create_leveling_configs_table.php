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
        if (!Schema::hasTable('leveling_configs') && Schema::hasTable('guilds')) {
            Schema::create('leveling_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            
            // Aktivierung
            $table->boolean('enabled')->default(false);
            
            // XP-Rate (0.25, 0.5, 0.75, 1, 1.5, 2, 2.5, 3)
            $table->decimal('xp_rate', 3, 2)->default(1.00);
            
            // Level-Up Ankündigung
            $table->string('level_up_channel_id')->nullable();
            $table->string('level_up_type')->default('channel'); // 'channel' oder 'dm'
            $table->text('level_up_message')->nullable();
            
            // Rollenbelohnungen
            $table->string('role_reward_type')->default('stack'); // 'stack' oder 'highest_only'
            $table->boolean('remove_role_on_xp_loss')->default(false);
            
            // Von XP ausgeschlossene Rollen
            $table->string('excluded_roles_type')->default('allow_all_except'); // 'deny_all_except' oder 'allow_all_except'
            $table->text('excluded_roles')->nullable(); // JSON array
            
            // Von XP ausgeschlossene Kanäle
            $table->string('excluded_channels_type')->default('allow_all_except'); // 'deny_all_except' oder 'allow_all_except'
            $table->text('excluded_channels')->nullable(); // JSON array
            
            $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leveling_configs');
    }
};

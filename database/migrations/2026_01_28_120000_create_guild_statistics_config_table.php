<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guild_statistics_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->cascadeOnDelete();
            $table->boolean('enabled')->default(false);
            $table->string('channel_id', 32)->nullable();
            $table->string('message_id', 32)->nullable();
            $table->string('category_id', 32)->nullable();
            $table->string('channel_name', 100)->default('📊 statistics');
            $table->boolean('stat_members')->default(true);
            $table->boolean('stat_joins')->default(true);
            $table->boolean('stat_leaves')->default(true);
            $table->boolean('stat_vc')->default(true);
            $table->boolean('stat_boosts')->default(true);
            $table->unsignedSmallInteger('update_interval_minutes')->default(10);
            $table->unsignedInteger('joins_24h')->default(0);
            $table->unsignedInteger('leaves_24h')->default(0);
            $table->timestamp('last_reset_at')->nullable();
            $table->timestamp('last_message_edit_at')->nullable();
            $table->timestamps();
            $table->unique('guild_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guild_statistics_config');
    }
};

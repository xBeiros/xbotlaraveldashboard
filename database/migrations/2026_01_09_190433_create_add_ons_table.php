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
        Schema::create('add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->string('addon_type'); // z.B. 'team_management'
            $table->boolean('enabled')->default(false);
            $table->timestamps();

            $table->unique(['guild_id', 'addon_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_ons');
    }
};

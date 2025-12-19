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
        Schema::table('welcome_configs', function (Blueprint $table) {
            $table->string('card_avatar_position')->default('top')->after('card_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('welcome_configs', function (Blueprint $table) {
            $table->dropColumn('card_avatar_position');
        });
    }
};

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
        if (Schema::hasTable('welcome_configs') && Schema::hasColumn('welcome_configs', 'card_background_image')) {
            Schema::table('welcome_configs', function (Blueprint $table) {
            $table->longText('card_background_image')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('welcome_configs') && Schema::hasColumn('welcome_configs', 'card_background_image')) {
            Schema::table('welcome_configs', function (Blueprint $table) {
            $table->string('card_background_image')->nullable()->change();
            });
        }
    }
};

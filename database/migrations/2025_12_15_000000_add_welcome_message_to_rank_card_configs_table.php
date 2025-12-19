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
        if (Schema::hasTable('rank_card_configs')) {
            Schema::table('rank_card_configs', function (Blueprint $table) {
            $table->text('welcome_message')->nullable()->after('progress_bar_color');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rank_card_configs')) {
            Schema::table('rank_card_configs', function (Blueprint $table) {
            $table->dropColumn('welcome_message');
            });
        }
    }
};


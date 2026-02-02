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
        if (Schema::hasTable('welcome_configs')) {
            Schema::table('welcome_configs', function (Blueprint $table) {
                $table->boolean('embed_author')->default(true)->after('use_embed');
            });
        }
        if (Schema::hasTable('goodbye_configs')) {
            Schema::table('goodbye_configs', function (Blueprint $table) {
                $table->boolean('embed_author')->default(true)->after('use_embed');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('welcome_configs')) {
            Schema::table('welcome_configs', function (Blueprint $table) {
                $table->dropColumn('embed_author');
            });
        }
        if (Schema::hasTable('goodbye_configs')) {
            Schema::table('goodbye_configs', function (Blueprint $table) {
                $table->dropColumn('embed_author');
            });
        }
    }
};

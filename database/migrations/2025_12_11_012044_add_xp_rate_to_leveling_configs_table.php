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
        if (Schema::hasTable('leveling_configs')) {
            Schema::table('leveling_configs', function (Blueprint $table) {
            // Prüfe ob xp_rate bereits existiert
            if (!Schema::hasColumn('leveling_configs', 'xp_rate')) {
                $table->decimal('xp_rate', 3, 2)->default(1.00)->after('enabled');
            }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('leveling_configs')) {
            Schema::table('leveling_configs', function (Blueprint $table) {
            if (Schema::hasColumn('leveling_configs', 'xp_rate')) {
                $table->dropColumn('xp_rate');
            }
            });
        }
    }
};

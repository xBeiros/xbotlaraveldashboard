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
            // Prüfe ob xp_rate existiert, wenn nicht, füge es hinzu
            if (!Schema::hasColumn('leveling_configs', 'xp_rate')) {
                $table->decimal('xp_rate', 3, 2)->default(1.00)->after('enabled');
            }
            
            // Füge min_xp, max_xp und cooldown_seconds hinzu, wenn sie noch nicht existieren
            if (!Schema::hasColumn('leveling_configs', 'min_xp')) {
                $table->integer('min_xp')->default(15)->after('xp_rate');
            }
            if (!Schema::hasColumn('leveling_configs', 'max_xp')) {
                $table->integer('max_xp')->default(25)->after('min_xp');
            }
            if (!Schema::hasColumn('leveling_configs', 'cooldown_seconds')) {
                $table->integer('cooldown_seconds')->default(60)->after('max_xp');
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
            $table->dropColumn(['min_xp', 'max_xp', 'cooldown_seconds']);
            });
        }
    }
};

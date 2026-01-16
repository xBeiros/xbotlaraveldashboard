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
        Schema::table('team_management_configs', function (Blueprint $table) {
            // Füge default_role_id hinzu, falls sie nicht existiert
            if (!Schema::hasColumn('team_management_configs', 'default_role_id')) {
                $table->string('default_role_id')->nullable()->after('channel_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_management_configs', function (Blueprint $table) {
            if (Schema::hasColumn('team_management_configs', 'default_role_id')) {
                $table->dropColumn('default_role_id');
            }
        });
    }
};

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
        if (Schema::hasTable('birthdays')) {
        Schema::table('birthdays', function (Blueprint $table) {
                if (!Schema::hasColumn('birthdays', 'user_id')) {
                    $table->string('user_id')->nullable()->after('guild_id');
                }
                if (!Schema::hasColumn('birthdays', 'birthday')) {
                    $table->date('birthday')->nullable()->after('user_id');
                }
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('birthdays')) {
        Schema::table('birthdays', function (Blueprint $table) {
                if (Schema::hasColumn('birthdays', 'user_id')) {
                    $table->dropColumn('user_id');
                }
                if (Schema::hasColumn('birthdays', 'birthday')) {
                    $table->dropColumn('birthday');
                }
        });
        }
    }
};

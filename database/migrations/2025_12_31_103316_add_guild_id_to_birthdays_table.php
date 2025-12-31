<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('birthdays')) {
            Schema::table('birthdays', function (Blueprint $table) {
                if (!Schema::hasColumn('birthdays', 'guild_id')) {
                    $table->foreignId('guild_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('birthdays', 'user_id')) {
                    $table->string('user_id')->nullable()->after('guild_id');
                }
                if (!Schema::hasColumn('birthdays', 'birthday')) {
                    $table->date('birthday')->nullable()->after('user_id');
                }
            });
            
            // Add foreign key constraint if guild_id column was added
            if (Schema::hasColumn('birthdays', 'guild_id')) {
                Schema::table('birthdays', function (Blueprint $table) {
                    $table->foreign('guild_id')->references('id')->on('guilds')->onDelete('cascade');
                });
            }
            
            // Add unique constraint if both columns exist
            if (Schema::hasColumn('birthdays', 'guild_id') && Schema::hasColumn('birthdays', 'user_id')) {
                Schema::table('birthdays', function (Blueprint $table) {
                    // Check if unique constraint doesn't exist
                    $indexes = DB::select("SHOW INDEXES FROM birthdays WHERE Key_name = 'birthdays_guild_id_user_id_unique'");
                    if (empty($indexes)) {
                        $table->unique(['guild_id', 'user_id']);
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('birthdays')) {
            Schema::table('birthdays', function (Blueprint $table) {
                if (Schema::hasColumn('birthdays', 'guild_id')) {
                    $table->dropForeign(['guild_id']);
                    $table->dropColumn('guild_id');
                }
            });
        }
    }
};

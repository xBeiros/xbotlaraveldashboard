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
        Schema::table('giveaways', function (Blueprint $table) {
            // Change prize_code from string to text (JSON)
            $table->text('prize_code')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('giveaways', function (Blueprint $table) {
            // Convert back to string
            $table->string('prize_code')->nullable()->change();
        });
    }
};

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
        if (!Schema::hasTable('birthday_configs')) {
            Schema::create('birthday_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->boolean('enabled')->default(false);
            $table->string('channel_id')->nullable(); // Discord Channel ID für Geburtstags-Nachrichten
            $table->string('role_id')->nullable(); // Discord Role ID die am Geburtstag vergeben wird
            $table->string('embed_title')->nullable();
            $table->text('embed_description')->nullable();
            $table->string('embed_color')->nullable();
            $table->string('embed_thumbnail')->nullable();
            $table->string('embed_image')->nullable();
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('birthdays')) {
            Schema::create('birthdays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->string('user_id'); // Discord User ID
            $table->date('birthday'); // Geburtsdatum (nur für Tag/Monat relevant)
            $table->timestamps();
            
            $table->unique(['guild_id', 'user_id']); // Ein User kann nur ein Geburtsdatum pro Guild haben
            });
        }

        if (!Schema::hasTable('birthday_processed')) {
            Schema::create('birthday_processed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->string('user_id'); // Discord User ID
            $table->date('processed_date'); // Datum an dem der Geburtstag verarbeitet wurde
            $table->timestamps();
            
            $table->unique(['guild_id', 'user_id', 'processed_date']); // Ein Geburtstag kann nur einmal pro Tag verarbeitet werden
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('birthday_processed');
        Schema::dropIfExists('birthdays');
        Schema::dropIfExists('birthday_configs');
    }
};

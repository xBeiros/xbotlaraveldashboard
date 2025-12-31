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
        Schema::create('birthdays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->string('user_id'); // Discord User ID
            $table->date('birthday'); // Geburtsdatum (nur Tag und Monat relevant)
            $table->string('birthday_role_id')->nullable(); // Discord Role ID für Geburtstagsrolle
            $table->string('channel_id')->nullable(); // Channel für Geburtstags-Embed
            $table->boolean('enabled')->default(true);
            $table->boolean('send_embed')->default(true);
            $table->text('embed_title')->nullable();
            $table->text('embed_description')->nullable();
            $table->string('embed_color')->nullable();
            $table->timestamps();
            
            $table->unique(['guild_id', 'user_id']); // Ein User kann nur einen Geburtstag pro Server haben
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('birthdays');
    }
};

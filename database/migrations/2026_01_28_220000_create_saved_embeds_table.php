<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_embeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->cascadeOnDelete();
            $table->string('name', 100); // z.B. "Ankündigung", "Slot 1"
            $table->text('content')->nullable(); // Nachricht über dem Embed
            $table->json('embed'); // title, description, color, author, image, thumbnail, footer, fields
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_embeds');
    }
};

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
        Schema::create('giveaways', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
            $table->string('channel_id'); // Discord Channel ID
            $table->string('message_id')->nullable(); // Discord Message ID
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('prize_type', ['code', 'role', 'custom'])->default('custom');
            $table->string('prize_code')->nullable(); // Code für Code-Gewinn
            $table->string('prize_role_id')->nullable(); // Discord Role ID
            $table->text('prize_custom')->nullable(); // Custom Preis-Beschreibung
            $table->integer('winner_count')->default(1);
            $table->timestamp('ends_at');
            $table->boolean('ended')->default(false);
            $table->text('winner_message')->nullable(); // Custom Gewinner-Nachricht
            $table->text('ticket_message')->nullable(); // Custom Ticket-Nachricht
            $table->timestamps();
        });

        Schema::create('giveaway_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('giveaway_id')->constrained('giveaways')->onDelete('cascade');
            $table->string('user_id'); // Discord User ID
            $table->timestamps();
            
            $table->unique(['giveaway_id', 'user_id']);
        });

        Schema::create('giveaway_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('giveaway_id')->constrained('giveaways')->onDelete('cascade');
            $table->string('user_id'); // Discord User ID
            $table->boolean('notified')->default(false);
            $table->boolean('ticket_created')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giveaway_winners');
        Schema::dropIfExists('giveaway_participants');
        Schema::dropIfExists('giveaways');
    }
};

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
        // Ticket Categories
        if (!Schema::hasTable('ticket_categories')) {
            Schema::create('ticket_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
                $table->string('name'); // Kategorie-Name (z.B. "Support", "Bug Report")
                $table->string('emoji')->nullable(); // Emoji für die Kategorie
                $table->string('description')->nullable(); // Beschreibung der Kategorie
                $table->string('category_id'); // Discord Kategorie-ID, wo Tickets erstellt werden
                $table->string('channel_name_format')->default('ticket-{user}'); // Format: ticket-{user}, support-{user}-{date}, etc.
                $table->json('supporter_roles')->nullable(); // Rollen, die Zugriff auf Tickets haben
                $table->boolean('enabled')->default(true);
                $table->integer('order')->default(0); // Reihenfolge im Dropdown
                $table->timestamps();
            });
        }

        // Tickets
        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
                $table->foreignId('category_id')->constrained('ticket_categories')->onDelete('cascade');
                $table->string('channel_id'); // Discord Channel-ID
                $table->string('user_id'); // Discord User-ID, der das Ticket erstellt hat
                $table->string('message_id')->nullable(); // Message-ID des Ticket-Posts
                $table->enum('status', ['open', 'closed', 'deleted'])->default('open');
                $table->timestamp('closed_at')->nullable();
                $table->string('closed_by')->nullable(); // User-ID, der das Ticket geschlossen hat
                $table->text('transcript')->nullable(); // HTML-Transcript
                $table->timestamps();
            });
        }

        // Ticket Members (User, die Zugriff auf das Ticket haben)
        if (!Schema::hasTable('ticket_members')) {
            Schema::create('ticket_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
                $table->string('user_id'); // Discord User-ID
                $table->enum('type', ['creator', 'invited', 'supporter'])->default('invited');
                $table->timestamps();
                
                $table->unique(['ticket_id', 'user_id']);
            });
        }

        // Ticket Posts (Konfiguration für den Ticket-Post)
        if (!Schema::hasTable('ticket_posts')) {
            Schema::create('ticket_posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guild_id')->constrained('guilds')->onDelete('cascade');
                $table->string('channel_id'); // Channel, wo der Post gesendet wird
                $table->string('message_id')->nullable(); // Message-ID des Posts
                $table->string('embed_title')->default('Ticket System');
                $table->text('embed_description')->nullable();
                $table->string('embed_color')->default('#ff0000');
                $table->string('embed_image')->nullable();
                $table->string('embed_banner')->nullable();
                $table->boolean('embed_footer')->default(true);
                $table->boolean('enabled')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_members');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('ticket_categories');
        Schema::dropIfExists('ticket_posts');
    }
};

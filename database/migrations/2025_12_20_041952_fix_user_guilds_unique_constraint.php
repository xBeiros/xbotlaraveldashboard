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
        // Bereinige Duplikate: Behalte nur den neuesten Eintrag für jede user_id + guild_id Kombination
        \DB::statement('
            DELETE ug1 FROM user_guilds ug1
            INNER JOIN user_guilds ug2 
            WHERE ug1.id < ug2.id 
            AND ug1.user_id = ug2.user_id 
            AND ug1.guild_id = ug2.guild_id
        ');
        
        Schema::table('user_guilds', function (Blueprint $table) {
            // Entferne den falschen Unique Constraint auf guild_id allein
            // (mehrere User können Zugriff auf denselben Server haben)
            try {
                $table->dropUnique(['guild_id']);
            } catch (\Exception $e) {
                // Constraint existiert möglicherweise nicht oder hat einen anderen Namen
                // Versuche alternative Namen
                try {
                    $table->dropUnique('user_guilds_guild_id_unique');
                } catch (\Exception $e2) {
                    // Ignoriere wenn Constraint nicht existiert
                }
            }
            
            // Füge einen Composite Unique Key auf (user_id, guild_id) hinzu
            // Dies erlaubt mehrere User pro Server, aber verhindert Duplikate für denselben User+Server
            $table->unique(['user_id', 'guild_id'], 'user_guilds_user_guild_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_guilds', function (Blueprint $table) {
            // Entferne den Composite Unique Key
            $table->dropUnique('user_guilds_user_guild_unique');
            
            // Stelle den alten Unique Constraint auf guild_id wieder her
            $table->unique('guild_id');
        });
    }
};

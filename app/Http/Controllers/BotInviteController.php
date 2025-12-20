<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Guild;
use App\Models\UserGuild;

class BotInviteController extends Controller
{
    public function invite(Request $request)
    {
        $clientId = env('DISCORD_BOT_CLIENT_ID');
        $guildId = $request->input('guild_id') ?? $request->query('guild_id');
        
        if (!$clientId) {
            return redirect()->route('dashboard')->with('error', 'Bot Client ID nicht konfiguriert');
        }

        // Prüfe ob User eingeloggt ist
        if (!Auth::check()) {
            return redirect()->route('discord.login')->with('error', 'Bitte zuerst einloggen');
        }

        // Prüfe ob User Zugriff auf diesen Server hat (wenn guild_id angegeben)
        if ($guildId) {
            $userGuild = UserGuild::where('guild_id', $guildId)
                ->where('user_id', Auth::id())
                ->first();
            
            if (!$userGuild) {
                return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server');
            }
        }

        // Berechtigungen für den Bot
        $permissionsValue = '8'; // Administrator
        
        // Bot-Einladungs-URL ohne redirect_uri (Discord Bot-Einladungen benötigen keine redirect_uri)
        $inviteUrl = "https://discord.com/api/oauth2/authorize?client_id={$clientId}&permissions={$permissionsValue}&scope=bot%20applications.commands";
        
        if ($guildId) {
            $inviteUrl .= "&guild_id={$guildId}";
        }
        
        return redirect($inviteUrl);
    }

    public function callback(Request $request)
    {
        $guildId = $request->input('guild_id');
        
        if ($guildId) {
            // Hole Server-Info vom User
            $userGuild = \App\Models\UserGuild::where('guild_id', $guildId)
                ->where('user_id', Auth::id())
                ->first();
            
            if ($userGuild) {
                // Markiere Server als "Bot eingeladen"
                $userGuild->update(['bot_joined' => true]);
                
                // Erstelle oder aktualisiere Guild in der guilds Tabelle
                Guild::updateOrCreate(
                    ['discord_id' => $guildId],
                    [
                        'name' => $userGuild->name,
                        'icon' => $userGuild->icon,
                        'owner_id' => Auth::user()->discord_id,
                        'bot_active' => true,
                    ]
                );
            }
        }
        
        return redirect()->route('dashboard')->with('success', 'Bot erfolgreich eingeladen!');
    }
}

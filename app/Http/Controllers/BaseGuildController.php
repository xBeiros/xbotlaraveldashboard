<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserGuild;
use App\Models\Guild;

/**
 * Base Controller für alle Guild-spezifischen Controller
 * Enthält gemeinsame Helper-Methoden für Authorization und Performance
 */
abstract class BaseGuildController extends Controller
{
    /**
     * Prüft ob User Zugriff auf Guild hat und gibt UserGuild zurück
     * 
     * @param string $guild Discord Guild ID
     * @return array ['userGuild' => UserGuild|null, 'error' => string|null]
     */
    protected function checkGuildAccess($guild)
    {
        $user = Auth::user();
        
        if (!$user) {
            return ['userGuild' => null, 'error' => redirect()->route('discord.login')];
        }

        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return ['userGuild' => null, 'error' => redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.')];
        }

        return ['userGuild' => $userGuild, 'error' => null];
    }

    /**
     * Prüft ob User Berechtigung zum Verwalten der Guild hat
     * 
     * @param UserGuild $userGuild
     * @return bool
     */
    protected function canManageGuild($permissions)
    {
        // Berechtigung: MANAGE_GUILD (0x20) oder Administrator (0x8)
        return ($permissions & 0x20) !== 0 || ($permissions & 0x8) !== 0;
    }

    /**
     * Lädt alle Guilds für Sidebar (mit Caching)
     * 
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    protected function getAllGuildsForSidebar($userId)
    {
        // Cache für 1 Minute um Performance zu verbessern
        return cache()->remember("user_guilds_sidebar_{$userId}", 60, function () use ($userId) {
            return UserGuild::where('user_id', $userId)
                ->where('bot_joined', true)
                ->get()
                ->filter(function ($g) {
                    return $this->canManageGuild($g->permissions);
                })
                ->sortBy('name')
                ->values()
                ->map(function ($g) {
                    return [
                        'id' => $g->guild_id,
                        'name' => $g->name,
                        'icon_url' => $g->icon ? "https://cdn.discordapp.com/icons/{$g->guild_id}/{$g->icon}.png" : null,
                        'owner' => $g->owner,
                        'bot_joined' => $g->bot_joined ?? false,
                    ];
                });
        });
    }

    /**
     * Lädt oder erstellt Guild Model
     * 
     * @param string $guild Discord Guild ID
     * @param UserGuild $userGuild
     * @param \App\Models\User $user
     * @return Guild
     */
    protected function getOrCreateGuildModel($guild, $userGuild, $user)
    {
        return Guild::firstOrCreate(
            ['discord_id' => $guild],
            [
                'name' => $userGuild->name,
                'bot_active' => true,
                'language' => 'de',
            ]
        );
    }
}


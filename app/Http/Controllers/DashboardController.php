<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\UserGuild;
use App\Models\Guild;
use App\Models\SocialNotification;
use App\Models\AutoModerationConfig;
use App\Models\LevelingConfig;
use App\Models\RoleReward;
use App\Models\RankCardConfig;
use App\Models\ReactionRole;
use App\Models\TicketCategory;
use App\Models\TicketPost;
use App\Models\Giveaway;
use App\Models\Birthday;
use App\Models\BirthdayConfig;
use App\Models\DashboardWidget;
use App\Models\Ticket;

class DashboardController extends BaseGuildController
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->discord_token) {
            return redirect()->route('discord.login')->with('error', 'Bitte mit Discord einloggen.');
        }

        // Force Refresh: Wenn ?refresh=1 Parameter vorhanden, synchronisiere sofort
        $forceRefresh = $request->has('refresh') && $request->get('refresh') == '1';
        
        // Performance: Hole Server-Liste nur alle 5 Minuten neu von Discord API
        // Verwende DB-Cache für schnellere Ladezeiten
        // Reduziert auf 5 Minuten, damit gelöschte Server schneller verschwinden
        $guildsCacheMinutes = 5;
        $oldestGuild = UserGuild::where('user_id', $user->id)
            ->orderBy('updated_at', 'asc')
            ->first();
        
        $shouldSyncGuilds = $forceRefresh || !$oldestGuild || 
            ($oldestGuild && now()->diffInMinutes($oldestGuild->updated_at) >= $guildsCacheMinutes);
        
        if ($shouldSyncGuilds) {
            // Hole Server vom User über Discord API (nur wenn nötig)
            try {
                $guilds = $this->fetchUserGuilds($user->discord_token);
                $discordGuildIds = collect($guilds)->pluck('id')->toArray();
                
                // Aktualisiere oder erstelle vorhandene Server
                foreach ($guilds as $guild) {
                    UserGuild::updateOrCreate(
                        ['user_id' => $user->id, 'guild_id' => $guild['id']],
                        [
                            'name' => $guild['name'],
                            'icon' => $guild['icon'] ?? null,
                            'owner' => $guild['owner'] ?? false,
                            'permissions' => $guild['permissions'] ?? 0,
                        ]
                    );
                }
                
                // WICHTIG: Entferne Server, die nicht mehr in der Discord-Liste sind
                // (z.B. wenn User den Server verlassen oder gelöscht hat)
                UserGuild::where('user_id', $user->id)
                    ->whereNotIn('guild_id', $discordGuildIds)
                    ->delete();
                    
            } catch (\Exception $e) {
                \Log::warning("Fehler beim Synchronisieren der Guilds: " . $e->getMessage());
                // Weiter mit DB-Daten
            }
        }

        // Lade Server aus Datenbank (schnell, keine API-Calls)
        $userGuilds = UserGuild::where('user_id', $user->id)
            ->orderBy('name')
            ->get();
        
        // Lade alle Guild-Models auf einmal (Eager Loading für bessere Performance)
        $guildIds = $userGuilds->pluck('guild_id')->toArray();
        $guildModels = Guild::whereIn('discord_id', $guildIds)
            ->get()
            ->keyBy('discord_id');
        
        // Bereite Daten für UI vor (keine API-Calls, nur DB)
        $userGuilds = $userGuilds->map(function ($guild) use ($guildModels) {
            // Prüfe zuerst user_guilds.bot_joined
            $botJoined = $guild->bot_joined ?? false;
            
            // Wenn bot_joined nicht gesetzt oder false, prüfe guilds.bot_active
            // Dies ist wichtig, da bot_joined manchmal nicht aktualisiert wird
            if ($botJoined === false || $botJoined === null) {
                $guildModel = $guildModels->get($guild->guild_id);
                if ($guildModel && $guildModel->bot_active === true) {
                    $botJoined = true;
                    // Aktualisiere auch user_guilds.bot_joined für zukünftige Abfragen
                    if ($guild->bot_joined !== true) {
                        $guild->update(['bot_joined' => true]);
                    }
                }
            }
            
            $canManage = $this->canManageGuild($guild->permissions);
            
            return [
                'id' => $guild->guild_id,
                'name' => $guild->name,
                'icon' => $guild->icon,
                'icon_url' => $guild->icon ? "https://cdn.discordapp.com/icons/{$guild->guild_id}/{$guild->icon}.png" : null,
                'owner' => $guild->owner,
                'permissions' => $guild->permissions,
                'bot_joined' => (bool) $botJoined, // Stelle sicher, dass es ein Boolean ist
                'can_manage' => $canManage,
            ];
        })
        ->filter(function ($guild) {
            return $guild['can_manage'];
        })
        ->values();

        // Bot-Status-Prüfung: NUR im Hintergrund, blockiert nicht die Seite
        // Wird asynchron nach dem Response ausgeführt
        $botClientId = config('services.discord.bot_client_id');
        $botToken = config('services.discord.bot_token');
        
        if ($botToken && $botClientId) {
            // Prüfe nur Server, deren Status älter als 10 Minuten ist
            $botCacheMinutes = 10;
            $guildsToCheck = collect($userGuilds)->filter(function ($guild) use ($botCacheMinutes) {
                $userGuild = UserGuild::where('guild_id', $guild['id'])->first();
                if (!$userGuild) return false;
                
                if ($userGuild->bot_joined === null) return true;
                return $userGuild->updated_at->addMinutes($botCacheMinutes)->isPast();
            });
            
            // Führe Prüfung im Hintergrund aus (nach Response, blockiert nicht)
            if ($guildsToCheck->isNotEmpty()) {
                $guildIds = $guildsToCheck->pluck('id')->toArray();
                
                // Verwende register_shutdown_function für asynchrone Ausführung
                register_shutdown_function(function () use ($guildIds, $botClientId, $botToken, $user) {
                    try {
                        $guildModels = UserGuild::whereIn('guild_id', $guildIds)
                            ->where('user_id', $user->id)
                            ->get();
                        
                        if ($guildModels->isNotEmpty()) {
                            $this->checkBotsOnGuildsParallel($guildModels, $botClientId, $botToken, $user);
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Fehler bei Hintergrund-Bot-Status-Prüfung: " . $e->getMessage());
                    }
                });
            }
        }

        // Lade Widgets für den User (globale Widgets ohne guild_id)
        $widgets = DashboardWidget::where('user_id', $user->id)
            ->whereNull('guild_id')
            ->where('enabled', true)
            ->orderBy('position')
            ->get()
            ->map(function ($widget) {
                return [
                    'id' => $widget->id,
                    'type' => $widget->widget_type,
                    'position' => $widget->position,
                    'column' => $widget->column,
                    'row' => $widget->row,
                    'config' => $widget->config ?? [],
                ];
            });

        return Inertia::render('Dashboard', [
            'guilds' => $userGuilds,
            'botClientId' => $botClientId,
            'widgets' => $widgets,
        ]);
    }

    private function fetchUserGuilds($token)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get('https://discord.com/api/users/@me/guilds');

            if ($response->successful()) {
                return $response->json();
            }
            
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function config($guild)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('discord.login');
        }

        // Prüfe ob User Zugriff auf diesen Server hat
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        // Prüfe ob Bot noch auf dem Server ist (blockiert nicht für Bot-Master)
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung (nur wenn User kein Bot-Master ist)
        }

        // Lade oder erstelle Guild-Model mit korrektem Bot-Status
        $guildModel = $this->getOrCreateGuildModel($guild, $userGuild, $user);

        // Lade oder erstelle Konfigurationen
        $welcomeConfig = $guildModel->welcomeConfig()->firstOrCreate([]);
        $goodbyeConfig = $guildModel->goodbyeConfig()->firstOrCreate([]);

        // Lade alle Guilds für Sidebar (mit Caching)
        $allGuilds = $this->getAllGuildsForSidebar($user->id);

        // Lade Kanäle vom Discord Server
        $channels = $this->fetchGuildChannels($guild);

        return Inertia::render('Guild/Config', [
            'guild' => [
                'id' => $userGuild->guild_id,
                'name' => $userGuild->name,
                'icon_url' => $userGuild->icon ? "https://cdn.discordapp.com/icons/{$userGuild->guild_id}/{$userGuild->icon}.png" : null,
                'bot_joined' => true,
            ],
            'guilds' => $allGuilds,
            'channels' => $channels,
            'welcomeConfig' => $welcomeConfig ? [
                'enabled' => $welcomeConfig->enabled,
                'channel_id' => $welcomeConfig->channel_id,
                'message' => $welcomeConfig->message,
                'use_embed' => $welcomeConfig->use_embed ?? false,
                'embed_title' => $welcomeConfig->embed_title,
                'embed_description' => $welcomeConfig->embed_description,
                'embed_color' => $welcomeConfig->embed_color,
                'embed_thumbnail' => $welcomeConfig->embed_thumbnail,
                'embed_image' => $welcomeConfig->embed_image,
                'embed_footer' => $welcomeConfig->embed_footer ?? true,
                'use_welcome_card' => $welcomeConfig->use_welcome_card ?? false,
                'card_font' => $welcomeConfig->card_font,
                'card_text_color' => $welcomeConfig->card_text_color ?? '#ffffff',
                'card_background_color' => $welcomeConfig->card_background_color ?? '#000000',
                'card_overlay_opacity' => $welcomeConfig->card_overlay_opacity ?? 50,
                'card_background_image' => $welcomeConfig->card_background_image,
                'card_title' => $welcomeConfig->card_title ?? '{user.idname} just joined the server',
                'card_avatar_position' => $welcomeConfig->card_avatar_position ?? 'top',
            ] : null,
            'goodbyeConfig' => $goodbyeConfig ? [
                'enabled' => $goodbyeConfig->enabled,
                'channel_id' => $goodbyeConfig->channel_id,
                'message' => $goodbyeConfig->message,
                'use_embed' => $goodbyeConfig->use_embed ?? false,
                'embed_title' => $goodbyeConfig->embed_title,
                'embed_description' => $goodbyeConfig->embed_description,
                'embed_color' => $goodbyeConfig->embed_color,
                'embed_thumbnail' => $goodbyeConfig->embed_thumbnail,
                'embed_image' => $goodbyeConfig->embed_image,
                'embed_footer' => $goodbyeConfig->embed_footer ?? true,
                'use_goodbye_card' => $goodbyeConfig->use_goodbye_card ?? false,
                'card_font' => $goodbyeConfig->card_font,
                'card_text_color' => $goodbyeConfig->card_text_color ?? '#ffffff',
                'card_background_color' => $goodbyeConfig->card_background_color ?? '#000000',
                'card_overlay_opacity' => $goodbyeConfig->card_overlay_opacity ?? 50,
                'card_background_image' => $goodbyeConfig->card_background_image,
                'card_title' => $goodbyeConfig->card_title ?? '{user.idname} left the server',
                'card_avatar_position' => $goodbyeConfig->card_avatar_position ?? 'top',
            ] : null,
        ]);
    }

    public function welcome($guild)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('discord.login');
        }

        // Prüfe ob User Zugriff auf diesen Server hat
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        // Prüfe ob Bot noch auf dem Server ist (blockiert nicht für Bot-Master)
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung (nur wenn User kein Bot-Master ist)
        }

        // Lade oder erstelle Guild-Model mit korrektem Bot-Status
        $guildModel = $this->getOrCreateGuildModel($guild, $userGuild, $user);

        // Lade oder erstelle Konfigurationen
        $welcomeConfig = $guildModel->welcomeConfig()->firstOrCreate([]);
        $goodbyeConfig = $guildModel->goodbyeConfig()->firstOrCreate([]);

        // Lade alle Guilds für Sidebar (mit Caching)
        $allGuilds = $this->getAllGuildsForSidebar($user->id);

        // Lade Kanäle vom Discord Server
        $channels = $this->fetchGuildChannels($guild);
        
        // Debug: Log Channels (nur in Entwicklung)
        if (config('app.debug')) {
            \Log::info("Welcome page - Channels loaded: " . count($channels) . " channels for guild {$guild}");
        }

        return Inertia::render('Guild/Welcome', [
            'guild' => [
                'id' => $userGuild->guild_id,
                'name' => $userGuild->name,
                'icon_url' => $userGuild->icon ? "https://cdn.discordapp.com/icons/{$userGuild->guild_id}/{$userGuild->icon}.png" : null,
                'bot_joined' => true,
            ],
            'guilds' => $allGuilds,
            'channels' => $channels,
            'welcomeConfig' => $welcomeConfig ? [
                'enabled' => $welcomeConfig->enabled,
                'channel_id' => $welcomeConfig->channel_id,
                'message' => $welcomeConfig->message,
                'use_embed' => $welcomeConfig->use_embed ?? false,
                'embed_title' => $welcomeConfig->embed_title,
                'embed_description' => $welcomeConfig->embed_description,
                'embed_color' => $welcomeConfig->embed_color,
                'embed_thumbnail' => $welcomeConfig->embed_thumbnail,
                'embed_image' => $welcomeConfig->embed_image,
                'embed_footer' => $welcomeConfig->embed_footer ?? true,
                'use_welcome_card' => $welcomeConfig->use_welcome_card ?? false,
                'card_font' => $welcomeConfig->card_font,
                'card_text_color' => $welcomeConfig->card_text_color ?? '#ffffff',
                'card_background_color' => $welcomeConfig->card_background_color ?? '#000000',
                'card_overlay_opacity' => $welcomeConfig->card_overlay_opacity ?? 50,
                'card_background_image' => $welcomeConfig->card_background_image,
                'card_title' => $welcomeConfig->card_title ?? '{user.idname} just joined the server',
                'card_avatar_position' => $welcomeConfig->card_avatar_position ?? 'top',
            ] : null,
            'goodbyeConfig' => $goodbyeConfig ? [
                'enabled' => $goodbyeConfig->enabled,
                'channel_id' => $goodbyeConfig->channel_id,
                'message' => $goodbyeConfig->message,
                'use_embed' => $goodbyeConfig->use_embed ?? false,
                'embed_title' => $goodbyeConfig->embed_title,
                'embed_description' => $goodbyeConfig->embed_description,
                'embed_color' => $goodbyeConfig->embed_color,
                'embed_thumbnail' => $goodbyeConfig->embed_thumbnail,
                'embed_image' => $goodbyeConfig->embed_image,
                'embed_footer' => $goodbyeConfig->embed_footer ?? true,
                'use_goodbye_card' => $goodbyeConfig->use_goodbye_card ?? false,
                'card_font' => $goodbyeConfig->card_font,
                'card_text_color' => $goodbyeConfig->card_text_color ?? '#ffffff',
                'card_background_color' => $goodbyeConfig->card_background_color ?? '#000000',
                'card_overlay_opacity' => $goodbyeConfig->card_overlay_opacity ?? 50,
                'card_background_image' => $goodbyeConfig->card_background_image,
                'card_title' => $goodbyeConfig->card_title ?? '{user.idname} left the server',
                'card_avatar_position' => $goodbyeConfig->card_avatar_position ?? 'top',
            ] : null,
        ]);
    }

    public function reactionRoles($guild)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('discord.login');
        }

        // Prüfe ob User Zugriff auf diesen Server hat
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        // Prüfe ob Bot noch auf dem Server ist (blockiert nicht für Bot-Master)
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung (nur wenn User kein Bot-Master ist)
        }

        // Lade oder erstelle Guild-Model mit korrektem Bot-Status
        $guildModel = $this->getOrCreateGuildModel($guild, $userGuild, $user);

        // Lade alle Guilds für Sidebar (mit Caching)
        $allGuilds = $this->getAllGuildsForSidebar($user->id);

        // Lade Kanäle und Rollen vom Discord Server
        $channels = $this->fetchGuildChannels($guild);
        $roles = $this->fetchGuildRoles($guild);
        
        // Lade Reaktionsrollen
        $reactionRoles = $guildModel->reactionRoles()->orderBy('created_at', 'desc')->get();

        return Inertia::render('Guild/ReactionRoles', [
            'guild' => [
                'id' => $userGuild->guild_id,
                'name' => $userGuild->name,
                'icon_url' => $userGuild->icon ? "https://cdn.discordapp.com/icons/{$userGuild->guild_id}/{$userGuild->icon}.png" : null,
                'bot_joined' => true,
            ],
            'guilds' => $allGuilds,
            'channels' => $channels,
            'roles' => $roles,
            'reactionRoles' => $reactionRoles->map(function ($rr) {
                return [
                    'id' => $rr->id,
                    'message_id' => $rr->message_id,
                    'channel_id' => $rr->channel_id,
                    'enabled' => $rr->enabled,
                    'embed_title' => $rr->embed_title,
                    'embed_description' => $rr->embed_description,
                    'embed_color' => $rr->embed_color,
                    'embed_thumbnail' => $rr->embed_thumbnail,
                    'embed_image' => $rr->embed_image,
                    'embed_banner' => $rr->embed_banner,
                    'embed_footer' => $rr->embed_footer,
                    'reactions' => $rr->reactions ?? [],
                ];
            }),
        ]);
    }

    public function serverManagement($guild)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('discord.login');
        }

        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        // Prüfe ob Bot noch auf dem Server ist (blockiert nicht für Bot-Master)
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung (nur wenn User kein Bot-Master ist)
        }

        // Lade oder erstelle Guild-Model mit korrektem Bot-Status
        $guildModel = $this->getOrCreateGuildModel($guild, $userGuild, $user);

        // Lade alle Guilds für Sidebar (mit Caching)
        $allGuilds = $this->getAllGuildsForSidebar($user->id);

        // Hole Bot-Info
        $botInfo = null;
        $botToken = config('services.discord.bot_token');
        return Inertia::render('Guild/ServerManagement', [
            'guild' => [
                'id' => $userGuild->guild_id,
                'name' => $userGuild->name,
                'icon_url' => $userGuild->icon ? "https://cdn.discordapp.com/icons/{$userGuild->guild_id}/{$userGuild->icon}.png" : null,
                'bot_joined' => true,
            ],
            'guilds' => $allGuilds,
            'guildModel' => [
                'prefix' => $guildModel->prefix,
                'bot_active' => $guildModel->bot_active ?? true,
                'language' => $guildModel->language ?? 'de',
                'timezone' => $guildModel->timezone ?? 'Europe/Berlin',
            ],
            'channels' => $this->fetchGuildChannels($guild),
            'categories' => $this->fetchGuildCategories($guild),
            'roles' => $this->fetchGuildRoles($guild),
            'ticketCategories' => $guildModel->ticketCategories()->orderBy('order')->get()->map(function ($tc) {
                return [
                    'id' => $tc->id,
                    'name' => $tc->name,
                    'emoji' => $tc->emoji,
                    'description' => $tc->description,
                    'welcome_message' => $tc->welcome_message ?? '',
                    'category_id' => $tc->category_id,
                    'channel_name_format' => $tc->channel_name_format,
                    'supporter_roles' => $tc->supporter_roles ?? [],
                    'enabled' => $tc->enabled,
                    'order' => $tc->order,
                ];
            }),
            'ticketPost' => $guildModel->ticketPosts()->first() ? [
                'id' => $guildModel->ticketPosts()->first()->id,
                'channel_id' => $guildModel->ticketPosts()->first()->channel_id,
                'message_id' => $guildModel->ticketPosts()->first()->message_id,
                'embed_title' => $guildModel->ticketPosts()->first()->embed_title,
                'embed_description' => $guildModel->ticketPosts()->first()->embed_description,
                'embed_color' => $guildModel->ticketPosts()->first()->embed_color,
                'embed_image' => $guildModel->ticketPosts()->first()->embed_image,
                'embed_banner' => $guildModel->ticketPosts()->first()->embed_banner,
                'embed_footer' => $guildModel->ticketPosts()->first()->embed_footer,
                'enabled' => $guildModel->ticketPosts()->first()->enabled,
            ] : null,
        ]);
    }

    public function deleteMessages($guild)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('discord.login');
        }

        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        // Prüfe ob Bot noch auf dem Server ist (blockiert nicht für Bot-Master)
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung (nur wenn User kein Bot-Master ist)
        }

        // Lade oder erstelle Guild-Model mit korrektem Bot-Status
        $guildModel = $this->getOrCreateGuildModel($guild, $userGuild, $user);

        // Lade alle Guilds für Sidebar (mit Caching)
        $allGuilds = $this->getAllGuildsForSidebar($user->id);

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
        return Inertia::render('Guild/DeleteMessages', [
            'guild' => [
                'id' => $userGuild->guild_id,
                'name' => $userGuild->name,
                'icon_url' => $userGuild->icon ? "https://cdn.discordapp.com/icons/{$userGuild->guild_id}/{$userGuild->icon}.png" : null,
                'bot_joined' => true,
            ],
            'guilds' => $allGuilds,
            'channels' => $this->fetchGuildChannels($guild),
            'autoDeletes' => $guildModel->autoDeleteMessages()->get()->map(function ($ad) {
                return [
                    'id' => $ad->id,
                    'channel_id' => $ad->channel_id,
                    'interval_minutes' => $ad->interval_minutes,
                    'enabled' => $ad->enabled,
                    'last_run_at' => $ad->last_run_at?->toDateTimeString(),
                ];
            }),
        ]);
    }

    public function ticketSystem($guild)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('discord.login');
        }
        $userGuild = UserGuild::where('user_id', $user->id)->where('guild_id', $guild)->first();
        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }
        
        // Prüfe ob Bot noch auf dem Server ist
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung
        }
        
        $guildModel = Guild::firstOrCreate(
            ['discord_id' => $guild],
            [
                'name' => $userGuild->name,
                'icon' => $userGuild->icon,
                'owner_id' => $user->discord_id,
                'bot_active' => true,
                'prefix' => '!',
            ]
        );
        
        // Aktualisiere bot_active Status
        $guildModel->update(['bot_active' => true]);
        
        // Lade alle Guilds für Sidebar (mit Caching)
        $allGuilds = $this->getAllGuildsForSidebar($user->id);
        $channels = $this->fetchGuildChannels($guild);
        $categories = $this->fetchGuildCategories($guild);
        $roles = $this->fetchGuildRoles($guild);
        $ticketCategories = $guildModel->ticketCategories()->orderBy('order')->get();
        $ticketPost = $guildModel->ticketPosts()->first();

        return Inertia::render('Guild/TicketSystem', [
            'guild' => [
                'id' => $userGuild->guild_id,
                'name' => $userGuild->name,
                'icon_url' => $userGuild->icon ? "https://cdn.discordapp.com/icons/{$userGuild->guild_id}/{$userGuild->icon}.png" : null,
                'owner' => $userGuild->owner,
                'bot_joined' => true,
            ],
            'guilds' => $allGuilds,
            'channels' => $channels,
            'categories' => $categories,
            'roles' => $roles,
            'ticketCategories' => $ticketCategories->map(function ($tc) {
                return [
                    'id' => $tc->id,
                    'name' => $tc->name,
                    'emoji' => $tc->emoji,
                    'description' => $tc->description,
                    'welcome_message' => $tc->welcome_message ?? '',
                    'category_id' => $tc->category_id,
                    'channel_name_format' => $tc->channel_name_format,
                    'supporter_roles' => $tc->supporter_roles,
                    'enabled' => $tc->enabled,
                    'order' => $tc->order,
                ];
            }),
            'ticketPost' => $ticketPost ? [
                'id' => $ticketPost->id,
                'channel_id' => $ticketPost->channel_id,
                'message_id' => $ticketPost->message_id,
                'embed_title' => $ticketPost->embed_title,
                'embed_description' => $ticketPost->embed_description,
                'embed_color' => $ticketPost->embed_color,
                'embed_image' => $ticketPost->embed_image,
                'embed_banner' => $ticketPost->embed_banner,
                'embed_footer' => $ticketPost->embed_footer,
                'enabled' => $ticketPost->enabled,
            ] : null,
            'ticketTranscripts' => $guildModel->tickets()
                ->where('status', 'closed')
                ->whereNotNull('transcript')
                ->whereNotNull('closed_at')
                ->orderBy('closed_at', 'desc')
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id' => $ticket->id,
                        'category_name' => $ticket->category->name ?? 'Unbekannt',
                        'user_id' => $ticket->user_id,
                        'closed_at' => $ticket->closed_at?->format('d.m.Y H:i'),
                        'closed_by' => $ticket->closed_by,
                        'has_transcript' => !empty($ticket->transcript),
                    ];
                }),
            'ticketTranscriptEnabled' => $guildModel->ticket_transcript_enabled ?? true,
            'ticketCloseConfig' => [
                'require_confirmation' => $guildModel->ticket_close_require_confirmation ?? false,
                'close_message' => $guildModel->ticket_close_message,
                'confirmation_button_text' => $guildModel->ticket_close_confirmation_button_text,
            ],
        ]);
    }

    public function social($guild)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('discord.login');
        }

        // Prüfe ob User Zugriff auf diesen Server hat
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        // Prüfe ob Bot noch auf dem Server ist (blockiert nicht für Bot-Master)
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung (nur wenn User kein Bot-Master ist)
        }

        // Lade oder erstelle Guild-Model mit korrektem Bot-Status
        $guildModel = $this->getOrCreateGuildModel($guild, $userGuild, $user);

        // Lade alle Guilds für Sidebar (mit Caching)
        $allGuilds = $this->getAllGuildsForSidebar($user->id);

        // Lade Kanäle vom Discord Server
        $channels = $this->fetchGuildChannels($guild);

        // Lade Social Notifications
        $socialNotifications = $guildModel->socialNotifications()
            ->orderBy('platform')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'platform' => $notification->platform,
                    'channel_id' => $notification->channel_id,
                    'username' => $notification->username,
                    'webhook_url' => $notification->webhook_url,
                    'enabled' => $notification->enabled,
                    'notify_live' => $notification->notify_live,
                    'custom_message' => $notification->custom_message,
                ];
            });

        return Inertia::render('Guild/Social', [
            'guild' => [
                'id' => $userGuild->guild_id,
                'name' => $userGuild->name,
                'icon_url' => $userGuild->icon ? "https://cdn.discordapp.com/icons/{$userGuild->guild_id}/{$userGuild->icon}.png" : null,
                'bot_joined' => true,
            ],
            'guilds' => $allGuilds,
            'channels' => $channels,
            'socialNotifications' => $socialNotifications,
        ]);
    }


    /**
     * Prüft ob Bot noch auf dem Server ist und aktualisiert den Status
     * Gibt true zurück wenn Bot auf Server ist oder User Bot-Master ist, sonst Redirect-Response
     */
    private function verifyAndUpdateBotStatus($guildId, $userGuild)
    {
        $botClientId = config('services.discord.bot_client_id');
        $botToken = config('services.discord.bot_token');
        
        if (!$botToken || !$botClientId) {
            // Wenn kein Token, können wir nicht prüfen - erlaube Zugriff
            if (config('app.debug')) {
                \Log::warning("verifyAndUpdateBotStatus: Bot-Token fehlt für Guild {$guildId}");
            }
            return true;
        }
        
        $botOnServer = $this->checkBotOnGuild($guildId, $botClientId, $botToken);
        
        // Aktualisiere Status unabhängig davon, ob Bot auf Server ist oder nicht
        Guild::where('discord_id', $guildId)->update(['bot_active' => $botOnServer]);
        $userGuild->update(['bot_joined' => $botOnServer]);
        
        if (!$botOnServer) {
            if (config('app.debug')) {
                \Log::info("verifyAndUpdateBotStatus: Bot nicht auf Server, Status aktualisiert", [
                    'guild_id' => $guildId,
                    'bot_joined' => false,
                ]);
            }
            
            // Wenn User Bot-Master ist (MANAGE_GUILD oder Administrator), erlaube Zugriff trotzdem
            // Der User kann dann den Bot einladen
            if ($this->canManageGuild($userGuild->permissions)) {
                // Bot-Master kann Server einrichten, auch wenn Bot nicht auf Server ist
                // Er wird dann zur Einladungsseite weitergeleitet oder kann den Bot einladen
                return true;
            }
            
            return redirect()->route('dashboard')->with('error', 'Der Bot ist nicht mehr auf diesem Server. Bitte lade den Bot erneut ein.');
        }
        
        if (config('app.debug')) {
            \Log::info("verifyAndUpdateBotStatus: Bot auf Server, Status aktualisiert", [
                'guild_id' => $guildId,
                'bot_joined' => true,
            ]);
        }
        
        return true;
    }

    /**
     * Helper-Methode: Lädt bot_joined Status nach verifyAndUpdateBotStatus
     * und erstellt/aktualisiert Guild-Model mit korrektem bot_active Status
     */
    protected function getOrCreateGuildModel($guildId, $userGuild, $user)
    {
        // Lade bot_joined Status nach verifyAndUpdateBotStatus (wurde dort bereits aktualisiert)
        $userGuild->refresh();
        $botOnServer = $userGuild->bot_joined ?? false;

        // Lade oder erstelle Guild aus Datenbank
        $guildModel = Guild::firstOrCreate(
            ['discord_id' => $guildId],
            [
                'name' => $userGuild->name,
                'icon' => $userGuild->icon,
                'owner_id' => $user->discord_id,
                'bot_active' => $botOnServer,
                'prefix' => '!',
            ]
        );
        
        // Aktualisiere bot_active Status basierend auf bot_joined (wurde bereits in verifyAndUpdateBotStatus gesetzt)
        $guildModel->update(['bot_active' => $botOnServer]);
        
        return $guildModel;
    }

    private function fetchGuildChannels($guildId)
    {
        $botToken = config('services.discord.bot_token');
        
        if (!$botToken) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->timeout(5)->get("https://discord.com/api/v10/guilds/{$guildId}/channels");

            if ($response->successful()) {
                $channels = $response->json();
                
                // Filtere nur Text-Kanäle und Kategorien, sortiere nach Position
                $allChannels = collect($channels)
                    ->filter(function ($channel) {
                        // Nur Text-Kanäle (type 0) und Kategorien (type 4)
                        return in_array($channel['type'], [0, 4]);
                    })
                    ->sortBy('position')
                    ->map(function ($channel) {
                        return [
                            'id' => $channel['id'],
                            'name' => $channel['name'],
                            'type' => $channel['type'], // 0 = Text, 4 = Category
                            'parent_id' => $channel['parent_id'] ?? null,
                            'position' => $channel['position'] ?? 0,
                            'is_category' => $channel['type'] === 4, // Markiere Kategorien explizit
                        ];
                    })
                    ->values();
                
                // Gruppiere Kanäle nach Kategorien
                $categories = $allChannels->where('type', 4)->keyBy('id');
                $textChannels = $allChannels->where('type', 0);
                
                $grouped = [];
                
                // Kanäle ohne Kategorie zuerst
                foreach ($textChannels->whereNull('parent_id') as $channel) {
                    $channel['is_category'] = false; // Stelle sicher, dass Text-Kanäle is_category = false haben
                    $grouped[] = $channel;
                }
                
                // Dann Kanäle nach Kategorien gruppiert
                foreach ($categories as $category) {
                    $categoryChannels = $textChannels->where('parent_id', $category['id']);
                    // Kategorie als Separator hinzufügen, auch wenn leer (für bessere Struktur)
                    $grouped[] = [
                        'id' => $category['id'],
                        'name' => $category['name'],
                        'type' => 4,
                        'is_category' => true,
                    ];
                    // Kanäle in dieser Kategorie
                    foreach ($categoryChannels as $channel) {
                        $channel['is_category'] = false; // Stelle sicher, dass Text-Kanäle is_category = false haben
                        $grouped[] = $channel;
                    }
                }
                
                return $grouped;
            }
            
            return [];
        } catch (\Exception $e) {
            \Log::warning("Failed to fetch channels for guild {$guildId}: " . $e->getMessage());
            return [];
        }
    }

    public function autoModeration($guild)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('discord.login');
        }

        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        // Prüfe ob Bot noch auf dem Server ist (blockiert nicht für Bot-Master)
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung (nur wenn User kein Bot-Master ist)
        }

        // Lade oder erstelle Guild-Model mit korrektem Bot-Status
        $guildModel = $this->getOrCreateGuildModel($guild, $userGuild, $user);

        // Lade alle Guilds für Sidebar (mit Caching)
        $allGuilds = $this->getAllGuildsForSidebar($user->id);

        $channels = $this->fetchGuildChannels($guild);
        $roles = $this->fetchGuildRoles($guild);
        $autoModConfig = $guildModel->autoModerationConfig()->firstOrCreate([]);

        return Inertia::render('Guild/AutoModeration', [
            'guild' => [
                'id' => $userGuild->guild_id,
                'name' => $userGuild->name,
                'icon_url' => $userGuild->icon ? "https://cdn.discordapp.com/icons/{$userGuild->guild_id}/{$userGuild->icon}.png" : null,
                'bot_joined' => true,
            ],
            'guilds' => $allGuilds,
            'channels' => $channels,
            'roles' => $roles,
            'autoModConfig' => [
                'bad_words_enabled' => $autoModConfig->bad_words_enabled ?? false,
                'bad_words_list' => $autoModConfig->bad_words_list ?? [],
                'bad_words_delete_message' => $autoModConfig->bad_words_delete_message ?? true,
                'bad_words_warning_message' => $autoModConfig->bad_words_warning_message ?? '⚠️ {user}, solche Aussagen sind auf diesem Server nicht gestattet!',
                'bad_words_use_embed' => $autoModConfig->bad_words_use_embed ?? false,
                'bad_words_embed_title' => $autoModConfig->bad_words_embed_title ?? '⚠️ Unerlaubte Sprache',
                'bad_words_embed_description' => $autoModConfig->bad_words_embed_description ?? '{user}, solche Aussagen sind auf diesem Server nicht gestattet!',
                'bad_words_embed_color' => $autoModConfig->bad_words_embed_color ?? '#ff0000',
                'bad_words_embed_footer' => $autoModConfig->bad_words_embed_footer ?? true,
                'block_discord_invites' => $autoModConfig->block_discord_invites ?? false,
                'block_discord_invites_delete_message' => $autoModConfig->block_discord_invites_delete_message ?? true,
                'block_discord_invites_warning_message' => $autoModConfig->block_discord_invites_warning_message ?? '⚠️ {user}, Discord-Einladungslinks sind auf diesem Server nicht erlaubt!',
                'block_discord_invites_use_embed' => $autoModConfig->block_discord_invites_use_embed ?? false,
                'block_discord_invites_embed_title' => $autoModConfig->block_discord_invites_embed_title ?? '⚠️ Discord-Einladungslinks nicht erlaubt',
                'block_discord_invites_embed_description' => $autoModConfig->block_discord_invites_embed_description ?? '{user}, Discord-Einladungslinks sind auf diesem Server nicht erlaubt!',
                'block_discord_invites_embed_color' => $autoModConfig->block_discord_invites_embed_color ?? '#ff0000',
                'block_discord_invites_embed_footer' => $autoModConfig->block_discord_invites_embed_footer ?? true,
                'whitelist_channels' => $autoModConfig->whitelist_channels ?? [],
                'whitelist_roles' => $autoModConfig->whitelist_roles ?? [],
            ],
        ]);
    }

    private function fetchGuildRoles($guildId)
    {
        $botToken = config('services.discord.bot_token');
        
        if (!$botToken) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->timeout(5)->get("https://discord.com/api/v10/guilds/{$guildId}/roles");

            if ($response->successful()) {
                $roles = $response->json();
                
                return collect($roles)
                    ->sortByDesc('position')
                    ->map(function ($role) {
                        return [
                            'id' => $role['id'],
                            'name' => $role['name'],
                            'color' => $role['color'] ?? 0,
                            'position' => $role['position'] ?? 0,
                        ];
                    })
                    ->values()
                    ->toArray();
            }
            
            return [];
        } catch (\Exception $e) {
            \Log::warning("Failed to fetch roles for guild {$guildId}: " . $e->getMessage());
            return [];
        }
    }

    private function fetchGuildMembers($guildId, $limit = 1000)
    {
        $botToken = config('services.discord.bot_token');
        
        if (!$botToken) {
            return [];
        }

        try {
            $members = [];
            $after = null;
            
            // Discord API erlaubt max 1000 Mitglieder pro Request
            // Wir holen alle Mitglieder in Batches
            do {
                $url = "https://discord.com/api/v10/guilds/{$guildId}/members?limit=1000";
                if ($after) {
                    $url .= "&after={$after}";
                }
                
                $response = Http::withHeaders([
                    'Authorization' => 'Bot ' . $botToken,
                ])->timeout(10)->get($url);

                if ($response->successful()) {
                    $batch = $response->json();
                    if (empty($batch)) {
                        break;
                    }
                    
                    foreach ($batch as $member) {
                        $user = $member['user'] ?? null;
                        if ($user && !($user['bot'] ?? false)) { // Nur echte User, keine Bots
                            $members[] = [
                                'id' => $user['id'],
                                'username' => $user['username'] ?? 'Unknown',
                                'discriminator' => $user['discriminator'] ?? '0000',
                                'display_name' => $member['nick'] ?? $user['username'] ?? 'Unknown',
                                'avatar' => $user['avatar'] ?? null,
                                'avatar_url' => $user['avatar'] 
                                    ? "https://cdn.discordapp.com/avatars/{$user['id']}/{$user['avatar']}.png?size=256"
                                    : "https://cdn.discordapp.com/embed/avatars/" . (($user['discriminator'] ?? '0000') % 5) . ".png",
                            ];
                        }
                        
                        // Setze after für nächsten Batch
                        if (isset($user['id'])) {
                            $after = $user['id'];
                        }
                    }
                    
                    // Wenn weniger als 1000 zurückgegeben wurden, sind wir fertig
                    if (count($batch) < 1000) {
                        break;
                    }
                } else {
                    break;
                }
            } while (count($members) < $limit && $after);
            
            // Sortiere nach Display-Name
            usort($members, function($a, $b) {
                return strcasecmp($a['display_name'], $b['display_name']);
            });
            
            return $members;
        } catch (\Exception $e) {
            \Log::warning("Failed to fetch members for guild {$guildId}: " . $e->getMessage());
            return [];
        }
    }

    private function fetchGuildCategories($guildId)
    {
        $botToken = config('services.discord.bot_token');
        
        if (!$botToken) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->timeout(5)->get("https://discord.com/api/v10/guilds/{$guildId}/channels");

            if ($response->successful()) {
                $channels = $response->json();
                
                // Filter nur Kategorien (type 4)
                return collect($channels)
                    ->filter(function ($channel) {
                        return $channel['type'] === 4; // Category channel
                    })
                    ->map(function ($category) {
                        return [
                            'id' => $category['id'],
                            'name' => $category['name'],
                            'position' => $category['position'] ?? 0,
                        ];
                    })
                    ->sortBy('position')
                    ->values()
                    ->toArray();
            }
            
            return [];
        } catch (\Exception $e) {
            \Log::warning("Failed to fetch categories for guild {$guildId}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Prüfe Bot-Status für mehrere Guilds parallel (Performance-Optimierung)
     * Verwendet die Bot-Guild-Liste statt Member-API (funktioniert auch ohne Admin-Rechte)
     */
    private function checkBotsOnGuildsParallel($guilds, $botClientId, $botToken, $user)
    {
        if ($guilds->isEmpty()) {
            return;
        }
        
        try {
            // Hole alle Guilds, auf denen der Bot ist (funktioniert unabhängig von Bot-Rechten)
            $botGuildsResponse = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->timeout(5)->get('https://discord.com/api/v10/users/@me/guilds');
            
            $botGuildIds = [];
            if ($botGuildsResponse->successful()) {
                $botGuilds = $botGuildsResponse->json();
                $botGuildIds = collect($botGuilds)->pluck('id')->toArray();
            }
            
            // Verarbeite Ergebnisse
            foreach ($guilds as $guild) {
                $guildId = $guild->guild_id;
                $botJoined = in_array($guildId, $botGuildIds);
                
                if ($botJoined) {
                    // Bot ist auf Server - aktualisiere Guild-Info falls vorhanden
                    $botGuildInfo = collect($botGuildsResponse->json())->firstWhere('id', $guildId);
                    
                    Guild::updateOrCreate(
                        ['discord_id' => $guildId],
                        [
                            'name' => $botGuildInfo['name'] ?? $guild->name,
                            'icon' => $botGuildInfo['icon'] ?? $guild->icon,
                            'owner_id' => $user->discord_id ?? null,
                            'bot_active' => true,
                            'prefix' => '!',
                        ]
                    );
                } else {
                    // Bot ist NICHT auf Server
                    Guild::where('discord_id', $guildId)->update(['bot_active' => false]);
                }
                
                // Aktualisiere user_guilds Tabelle
                $guild->update(['bot_joined' => $botJoined]);
            }
        } catch (\Exception $e) {
            \Log::warning("Fehler bei paralleler Bot-Status-Prüfung: " . $e->getMessage());
            // Fallback: Prüfe einzeln (verwendet auch Bot-Guild-Liste)
            foreach ($guilds as $guild) {
                try {
                    $apiCheck = $this->checkBotOnGuild($guild->guild_id, $botClientId, $botToken);
                    $guild->update(['bot_joined' => $apiCheck]);
                    if ($apiCheck) {
                        Guild::updateOrCreate(
                            ['discord_id' => $guild->guild_id],
                            [
                                'name' => $guild->name,
                                'icon' => $guild->icon,
                                'owner_id' => $user->discord_id ?? null,
                                'bot_active' => true,
                                'prefix' => '!',
                            ]
                        );
                    } else {
                        Guild::where('discord_id', $guild->guild_id)->update(['bot_active' => false]);
                    }
                } catch (\Exception $guildError) {
                    \Log::warning("Fehler bei Einzelprüfung für Guild {$guild->guild_id}: " . $guildError->getMessage());
                }
            }
        }
    }

    private function checkBotOnGuild($guildId, $botClientId, $botToken)
    {
        if (!$botToken) {
            \Log::warning("Bot Token nicht verfügbar für Guild-Prüfung", ['guild_id' => $guildId]);
            return false;
        }

        try {
            // Verwende Bot-Guild-Liste statt Member-API (funktioniert auch ohne Admin-Rechte)
            $botGuildsResponse = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->timeout(5)->get('https://discord.com/api/v10/users/@me/guilds');
            
            if ($botGuildsResponse->successful()) {
                $botGuilds = $botGuildsResponse->json();
                $botGuildIds = collect($botGuilds)->pluck('id')->toArray();
                
                // Prüfe ob Bot auf diesem Server ist
                return in_array($guildId, $botGuildIds);
            }
            
            // Fallback: Versuche Member-API (kann bei fehlenden Rechten fehlschlagen)
            try {
                $memberResponse = Http::withHeaders([
                    'Authorization' => 'Bot ' . $botToken,
                ])->timeout(3)->get("https://discord.com/api/v10/guilds/{$guildId}/members/{$botClientId}");
                
                if ($memberResponse->successful()) {
                    return true;
                }
            } catch (\Exception $memberError) {
                \Log::debug("Member API check failed for guild {$guildId}: " . $memberError->getMessage());
            }
            
            return false;
        } catch (\Exception $e) {
            // Bei Fehler (z.B. Bot nicht auf Server, Timeout), return false
            \Log::debug("Bot check failed for guild {$guildId}: " . $e->getMessage());
            return false;
        }
    }

    public function leveling($guild)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('discord.login');
        }

        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        // Prüfe ob Bot noch auf dem Server ist (blockiert nicht für Bot-Master)
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung (nur wenn User kein Bot-Master ist)
        }

        // Lade oder erstelle Guild-Model mit korrektem Bot-Status
        $guildModel = $this->getOrCreateGuildModel($guild, $userGuild, $user);

        // Lade alle Guilds für Sidebar (mit Caching)
        $allGuilds = $this->getAllGuildsForSidebar($user->id);

        $channels = $this->fetchGuildChannels($guild);
        $roles = $this->fetchGuildRoles($guild);
        
        $levelingConfig = $guildModel->levelingConfig()->firstOrCreate([]);
        $rankCardConfig = $guildModel->rankCardConfig()->firstOrCreate([]);
        $roleRewards = $guildModel->roleRewards()->orderBy('level')->get();

        return Inertia::render('Guild/Leveling', [
            'guild' => [
                'id' => $userGuild->guild_id,
                'name' => $userGuild->name,
                'icon_url' => $userGuild->icon ? "https://cdn.discordapp.com/icons/{$userGuild->guild_id}/{$userGuild->icon}.png" : null,
                'bot_joined' => true,
            ],
            'guilds' => $allGuilds,
            'channels' => $channels,
            'roles' => $roles,
            'levelingConfig' => [
                'enabled' => $levelingConfig->enabled ?? false,
                'xp_rate' => (float)($levelingConfig->xp_rate ?? 1.00),
                'min_xp' => $levelingConfig->min_xp ?? 15,
                'max_xp' => $levelingConfig->max_xp ?? 25,
                'cooldown_seconds' => $levelingConfig->cooldown_seconds ?? 60,
                'level_up_channel_id' => $levelingConfig->level_up_channel_id,
                'level_up_type' => $levelingConfig->level_up_type ?? 'current_channel',
                'level_up_message' => $levelingConfig->level_up_message ?? 'GG {player}, you just advanced to level {level}!',
                'role_reward_type' => $levelingConfig->role_reward_type ?? 'stack',
                'remove_role_on_xp_loss' => $levelingConfig->remove_role_on_xp_loss ?? false,
                'excluded_roles_type' => $levelingConfig->excluded_roles_type ?? 'allow_all_except',
                'excluded_roles' => $levelingConfig->excluded_roles ?? [],
                'excluded_channels_type' => $levelingConfig->excluded_channels_type ?? 'allow_all_except',
                'excluded_channels' => $levelingConfig->excluded_channels ?? [],
            ],
            'rankCardConfig' => [
                'background_type' => $rankCardConfig->background_type ?? 'color',
                'background_color' => $rankCardConfig->background_color ?? '#000000',
                'background_image' => $rankCardConfig->background_image,
                'custom_background_url' => $rankCardConfig->custom_background_url,
                'overlay_opacity' => $rankCardConfig->overlay_opacity ?? 0,
                'text_color' => $rankCardConfig->text_color ?? '#ffffff',
                'rank_text_color' => $rankCardConfig->rank_text_color ?? '#ffffff',
                'level_text_color' => $rankCardConfig->level_text_color ?? '#5865f2',
                'xp_text_color' => $rankCardConfig->xp_text_color ?? '#ffffff',
                'progress_bar_color' => $rankCardConfig->progress_bar_color ?? '#5865f2',
            ],
            'roleRewards' => $roleRewards->map(function ($reward) {
                return [
                    'id' => $reward->id,
                    'level' => $reward->level,
                    'role_id' => $reward->role_id,
                ];
            }),
        ]);
    }

    public function giveaway($guild)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('discord.login');
        }

        // Prüfe ob User Zugriff auf diesen Server hat
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        // Prüfe ob Bot noch auf dem Server ist (blockiert nicht für Bot-Master)
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung (nur wenn User kein Bot-Master ist)
        }

        // Lade oder erstelle Guild-Model mit korrektem Bot-Status
        $guildModel = $this->getOrCreateGuildModel($guild, $userGuild, $user);

        // Lade alle Guilds für Sidebar (mit Caching)
        $allGuilds = $this->getAllGuildsForSidebar($user->id);

        // Lade Giveaways
        $giveaways = $guildModel->giveaways()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($g) {
                return [
                    'id' => $g->id,
                    'title' => $g->title,
                    'description' => $g->description,
                    'prize_type' => $g->prize_type,
                    'prize_code' => $g->prize_code,
                    'prize_role_id' => $g->prize_role_id,
                    'prize_custom' => $g->prize_custom,
                    'winner_count' => $g->winner_count,
                    'ends_at' => $g->ends_at,
                    'ended' => $g->ended,
                    'channel_id' => $g->channel_id,
                    'message_id' => $g->message_id,
                    'winner_message' => $g->winner_message,
                    'ticket_message' => $g->ticket_message,
                    'participant_count' => $g->participants()->count(),
                ];
            });

        return Inertia::render('Guild/Giveaway', [
            'guild' => [
                'id' => $userGuild->guild_id,
                'name' => $userGuild->name,
                'icon_url' => $userGuild->icon ? "https://cdn.discordapp.com/icons/{$userGuild->guild_id}/{$userGuild->icon}.png" : null,
                'bot_joined' => true,
            ],
            'guilds' => $allGuilds,
            'channels' => $this->fetchGuildChannels($guild),
            'roles' => $this->fetchGuildRoles($guild),
            'giveaways' => $giveaways,
        ]);
    }

    public function birthdays($guild)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('discord.login');
        }

        // Prüfe ob User Zugriff auf diesen Server hat
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        // Prüfe ob Bot noch auf dem Server ist (blockiert nicht für Bot-Master)
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung (nur wenn User kein Bot-Master ist)
        }

        // Lade oder erstelle Guild-Model mit korrektem Bot-Status
        $guildModel = $this->getOrCreateGuildModel($guild, $userGuild, $user);

        // Lade alle Guilds für Sidebar
        $allGuilds = UserGuild::where('user_id', $user->id)
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

        // Lade Kanäle, Rollen und Mitglieder
        $channels = $this->fetchGuildChannels($guild);
        $roles = $this->fetchGuildRoles($guild);
        $members = $this->fetchGuildMembers($guild);

        // Lade Geburtstage
        $birthdays = $guildModel->birthdays()
            ->orderBy('birthday', 'asc')
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'user_id' => $b->user_id,
                    'birthday' => $b->birthday->format('Y-m-d'),
                ];
            });

        // Lade Geburtstags-Konfiguration
        $birthdayConfig = $guildModel->birthdayConfig;
        $config = [
            'enabled' => $birthdayConfig->enabled ?? false,
            'channel_id' => $birthdayConfig->channel_id ?? null,
            'role_id' => $birthdayConfig->role_id ?? null,
            'embed_title' => $birthdayConfig->embed_title ?? null,
            'embed_description' => $birthdayConfig->embed_description ?? null,
            'embed_color' => $birthdayConfig->embed_color ?? '#5865f2',
            'embed_thumbnail' => $birthdayConfig->embed_thumbnail ?? null,
            'embed_image' => $birthdayConfig->embed_image ?? null,
        ];

        return Inertia::render('Guild/Birthdays', [
            'guild' => [
                'id' => $userGuild->guild_id,
                'name' => $userGuild->name,
                'icon_url' => $userGuild->icon ? "https://cdn.discordapp.com/icons/{$userGuild->guild_id}/{$userGuild->icon}.png" : null,
                'bot_joined' => true,
            ],
            'guilds' => $allGuilds,
            'channels' => $channels,
            'roles' => $roles,
            'members' => $members,
            'birthdays' => $birthdays,
            'birthdayConfig' => $config,
        ]);
    }

    /**
     * Store a new dashboard widget
     */
    public function storeWidget(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'widget_type' => 'required|string|in:members,tickets,giveaways,leveling',
            'guild_id' => 'nullable|string',
            'position' => 'nullable|integer|min:0',
            'column' => 'nullable|integer|min:0',
            'row' => 'nullable|integer|min:0',
            'config' => 'nullable|array',
        ]);

        // Prüfe ob bereits ein Widget dieses Typs für diese Guild existiert
        $existing = DashboardWidget::where('user_id', $user->id)
            ->where('guild_id', $validated['guild_id'] ?? null)
            ->where('widget_type', $validated['widget_type'])
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Widget dieses Typs existiert bereits'], 400);
        }

        $widget = DashboardWidget::create([
            'user_id' => $user->id,
            'guild_id' => $validated['guild_id'] ?? null,
            'widget_type' => $validated['widget_type'],
            'position' => $validated['position'] ?? 0,
            'column' => $validated['column'] ?? 0,
            'row' => $validated['row'] ?? 0,
            'config' => $validated['config'] ?? [],
            'enabled' => true,
        ]);

        return response()->json($widget);
    }

    /**
     * Update a dashboard widget
     */
    public function updateWidget(Request $request, $id)
    {
        $user = Auth::user();
        
        $widget = DashboardWidget::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'position' => 'nullable|integer|min:0',
            'column' => 'nullable|integer|min:0',
            'row' => 'nullable|integer|min:0',
            'config' => 'nullable|array',
            'enabled' => 'nullable|boolean',
        ]);

        $widget->update($validated);

        return response()->json($widget);
    }

    /**
     * Delete a dashboard widget
     */
    public function deleteWidget($id)
    {
        $user = Auth::user();
        
        $widget = DashboardWidget::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $widget->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Reorder widgets
     */
    public function reorderWidgets(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|integer|exists:dashboard_widgets,id',
            'widgets.*.position' => 'required|integer|min:0',
            'widgets.*.column' => 'required|integer|min:0',
            'widgets.*.row' => 'required|integer|min:0',
        ]);

        foreach ($validated['widgets'] as $widgetData) {
            DashboardWidget::where('user_id', $user->id)
                ->where('id', $widgetData['id'])
                ->update([
                    'position' => $widgetData['position'],
                    'column' => $widgetData['column'],
                    'row' => $widgetData['row'],
                ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get widget data based on type
     */
    public function getWidgetData($type, Request $request)
    {
        $user = Auth::user();
        $guildId = $request->get('guild_id');

        switch ($type) {
            case 'members':
                if (!$guildId) {
                    return response()->json(['error' => 'Guild ID required'], 400);
                }
                $guildModel = Guild::where('discord_id', $guildId)->first();
                if (!$guildModel) {
                    return response()->json(['count' => 0]);
                }
                // Hole Mitgliederanzahl über Discord API
                $botToken = config('services.discord.bot_token');
                if ($botToken) {
                    try {
                        $response = Http::withHeaders([
                            'Authorization' => 'Bot ' . $botToken,
                        ])->timeout(5)->get("https://discord.com/api/v10/guilds/{$guildId}?with_counts=true");
                        
                        if ($response->successful()) {
                            $guildData = $response->json();
                            return response()->json(['count' => $guildData['approximate_member_count'] ?? 0]);
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Failed to fetch member count: " . $e->getMessage());
                    }
                }
                return response()->json(['count' => 0]);
                
            case 'tickets':
                if (!$guildId) {
                    return response()->json(['error' => 'Guild ID required'], 400);
                }
                $guildModel = Guild::where('discord_id', $guildId)->first();
                if (!$guildModel) {
                    return response()->json(['count' => 0]);
                }
                $count = Ticket::where('guild_id', $guildModel->id)
                    ->where('status', 'open')
                    ->count();
                return response()->json(['count' => $count]);
                
            case 'giveaways':
                if (!$guildId) {
                    return response()->json(['error' => 'Guild ID required'], 400);
                }
                $guildModel = Guild::where('discord_id', $guildId)->first();
                if (!$guildModel) {
                    return response()->json(['count' => 0]);
                }
                $count = Giveaway::where('guild_id', $guildModel->id)
                    ->where('ended', false)
                    ->count();
                return response()->json(['count' => $count]);
                
            case 'leveling':
                if (!$guildId) {
                    return response()->json(['error' => 'Guild ID required'], 400);
                }
                $guildModel = Guild::where('discord_id', $guildId)->first();
                if (!$guildModel) {
                    return response()->json(['count' => 0]);
                }
                // Zähle User im Leveling-System
                $count = DB::table('user_levels')
                    ->where('guild_id', $guildModel->id)
                    ->distinct('user_id')
                    ->count('user_id');
                return response()->json(['count' => $count]);
                
            default:
                return response()->json(['error' => 'Invalid widget type'], 400);
        }
    }
}

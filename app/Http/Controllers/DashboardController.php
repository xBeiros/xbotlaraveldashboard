<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user || !$user->discord_token) {
            return redirect()->route('discord.login')->with('error', 'Bitte mit Discord einloggen.');
        }

        // Hole Server vom User über Discord API
        $guilds = $this->fetchUserGuilds($user->discord_token);
        
        // Speichere Server in Datenbank (Cache)
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

        // Prüfe auch über Discord API, ob Bot auf den Servern ist
        $botClientId = env('DISCORD_BOT_CLIENT_ID');
        $botToken = env('DISCORD_BOT_TOKEN');
        
        // Lade Server aus Datenbank und filtere nur die, wo User Rechte hat
        $userGuilds = UserGuild::where('user_id', $user->id)
            ->orderBy('name')
            ->get()
            ->map(function ($guild) use ($botClientId, $botToken, $user) {
                // Starte mit dem Status aus der DB (user_guilds.bot_joined)
                $botJoined = $guild->bot_joined ?? false;
                
                // IMMER prüfen über Discord API, wenn Bot-Token vorhanden ist
                // Dies stellt sicher, dass der Status immer aktuell ist, auch wenn der Bot gekickt wurde
                if ($botToken && $botClientId) {
                    $apiCheck = $this->checkBotOnGuild($guild->guild_id, $botClientId, $botToken);
                    
                    // API-Check hat Vorrang - aktualisiere Status basierend auf API-Ergebnis
                    if ($apiCheck) {
                        // Bot ist auf Server - aktualisiere DB
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
                        $botJoined = true;
                    } else {
                        // Bot ist NICHT auf Server - aktualisiere DB
                        Guild::where('discord_id', $guild->guild_id)->update(['bot_active' => false]);
                        $botJoined = false;
                    }
                } else {
                    // Wenn kein Token vorhanden, verwende DB-Status als Fallback
                    $botGuildIds = Guild::where('bot_active', true)->pluck('discord_id')->toArray();
                    $botJoined = in_array($guild->guild_id, $botGuildIds);
                }
                
                $canManage = $this->canManageGuild($guild->permissions);
                
                // Update bot_joined Status in user_guilds Tabelle (wichtig für UI)
                if ($guild->bot_joined !== $botJoined) {
                    $guild->update(['bot_joined' => $botJoined]);
                }
                
                return [
                    'id' => $guild->guild_id,
                    'name' => $guild->name,
                    'icon' => $guild->icon,
                    'icon_url' => $guild->icon ? "https://cdn.discordapp.com/icons/{$guild->guild_id}/{$guild->icon}.png" : null,
                    'owner' => $guild->owner,
                    'permissions' => $guild->permissions,
                    'bot_joined' => $botJoined,
                    'can_manage' => $canManage,
                ];
            })
            ->filter(function ($guild) {
                // Nur Server anzeigen, wo User Rechte hat
                return $guild['can_manage'];
            })
            ->values();

        return Inertia::render('Dashboard', [
            'guilds' => $userGuilds,
            'botClientId' => env('DISCORD_BOT_CLIENT_ID'),
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

        // Prüfe ob Bot noch auf dem Server ist
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung
        }

        // Lade oder erstelle Guild aus Datenbank
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

        // Aktualisiere bot_joined Status
        $userGuild->update(['bot_joined' => true]);

        // Lade oder erstelle Konfigurationen
        $welcomeConfig = $guildModel->welcomeConfig()->firstOrCreate([]);
        $goodbyeConfig = $guildModel->goodbyeConfig()->firstOrCreate([]);

        // Lade alle Guilds für Sidebar
        $allGuilds = UserGuild::where('user_id', $user->id)
            ->where('bot_joined', true)
            ->orderBy('name')
            ->get()
            ->map(function ($g) {
                return [
                    'id' => $g->guild_id,
                    'name' => $g->name,
                    'icon_url' => $g->icon ? "https://cdn.discordapp.com/icons/{$g->guild_id}/{$g->icon}.png" : null,
                    'owner' => $g->owner,
                ];
            });

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

        // Prüfe ob Bot noch auf dem Server ist
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung
        }

        // Lade oder erstelle Guild aus Datenbank
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

        // Lade oder erstelle Konfigurationen
        $welcomeConfig = $guildModel->welcomeConfig()->firstOrCreate([]);
        $goodbyeConfig = $guildModel->goodbyeConfig()->firstOrCreate([]);

        // Lade alle Guilds für Sidebar
        $allGuilds = UserGuild::where('user_id', $user->id)
            ->where('bot_joined', true)
            ->orderBy('name')
            ->get()
            ->map(function ($g) {
                return [
                    'id' => $g->guild_id,
                    'name' => $g->name,
                    'icon_url' => $g->icon ? "https://cdn.discordapp.com/icons/{$g->guild_id}/{$g->icon}.png" : null,
                    'owner' => $g->owner,
                ];
            });

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

        // Prüfe ob Bot noch auf dem Server ist
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung
        }

        // Lade oder erstelle Guild aus Datenbank
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

        // Lade alle Guilds für Sidebar
        $allGuilds = UserGuild::where('user_id', $user->id)
            ->where('bot_joined', true)
            ->orderBy('name')
            ->get()
            ->map(function ($g) {
                return [
                    'id' => $g->guild_id,
                    'name' => $g->name,
                    'icon_url' => $g->icon ? "https://cdn.discordapp.com/icons/{$g->guild_id}/{$g->icon}.png" : null,
                    'owner' => $g->owner,
                ];
            });

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

        $allGuilds = UserGuild::where('user_id', $user->id)
            ->where('bot_joined', true)
            ->orderBy('name')
            ->get()
            ->map(function ($g) {
                return [
                    'id' => $g->guild_id,
                    'name' => $g->name,
                    'icon_url' => $g->icon ? "https://cdn.discordapp.com/icons/{$g->guild_id}/{$g->icon}.png" : null,
                    'owner' => $g->owner,
                ];
            });

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
        
        $allGuilds = UserGuild::where('user_id', $user->id)->where('bot_joined', true)->orderBy('name')->get()->map(function ($g) {
            return [
                'id' => $g->guild_id,
                'name' => $g->name,
                'icon_url' => $g->icon ? "https://cdn.discordapp.com/icons/{$g->guild_id}/{$g->icon}.png" : null,
                'owner' => $g->owner,
            ];
        });
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

        // Prüfe ob Bot noch auf dem Server ist
        $botCheck = $this->verifyAndUpdateBotStatus($guild, $userGuild);
        if ($botCheck !== true) {
            return $botCheck; // Redirect mit Fehlermeldung
        }

        // Lade oder erstelle Guild aus Datenbank
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

        // Lade alle Guilds für Sidebar
        $allGuilds = UserGuild::where('user_id', $user->id)
            ->where('bot_joined', true)
            ->orderBy('name')
            ->get()
            ->map(function ($g) {
                return [
                    'id' => $g->guild_id,
                    'name' => $g->name,
                    'icon_url' => $g->icon ? "https://cdn.discordapp.com/icons/{$g->guild_id}/{$g->icon}.png" : null,
                    'owner' => $g->owner,
                ];
            });

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

    private function canManageGuild($permissions)
    {
        // Berechtigung: MANAGE_GUILD (0x20) oder Administrator (0x8)
        return ($permissions & 0x20) !== 0 || ($permissions & 0x8) !== 0;
    }

    /**
     * Prüft ob Bot noch auf dem Server ist und aktualisiert den Status
     * Gibt true zurück wenn Bot auf Server ist, sonst Redirect-Response
     */
    private function verifyAndUpdateBotStatus($guildId, $userGuild)
    {
        $botClientId = env('DISCORD_BOT_CLIENT_ID');
        $botToken = env('DISCORD_BOT_TOKEN');
        
        if (!$botToken || !$botClientId) {
            // Wenn kein Token, können wir nicht prüfen - erlaube Zugriff
            return true;
        }
        
        $botOnServer = $this->checkBotOnGuild($guildId, $botClientId, $botToken);
        
        if (!$botOnServer) {
            // Bot nicht mehr auf Server - aktualisiere Status
            Guild::where('discord_id', $guildId)->update(['bot_active' => false]);
            $userGuild->update(['bot_joined' => false]);
            
            return redirect()->route('dashboard')->with('error', 'Der Bot ist nicht mehr auf diesem Server. Bitte lade den Bot erneut ein.');
        }
        
        // Bot ist auf Server - aktualisiere Status
        Guild::where('discord_id', $guildId)->update(['bot_active' => true]);
        $userGuild->update(['bot_joined' => true]);
        
        return true;
    }

    private function fetchGuildChannels($guildId)
    {
        $botToken = env('DISCORD_BOT_TOKEN');
        
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
                        ];
                    })
                    ->values();
                
                // Gruppiere Kanäle nach Kategorien
                $categories = $allChannels->where('type', 4)->keyBy('id');
                $textChannels = $allChannels->where('type', 0);
                
                $grouped = [];
                
                // Kanäle ohne Kategorie zuerst
                foreach ($textChannels->whereNull('parent_id') as $channel) {
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

        $allGuilds = UserGuild::where('user_id', $user->id)
            ->where('bot_joined', true)
            ->orderBy('name')
            ->get()
            ->map(function ($g) {
                return [
                    'id' => $g->guild_id,
                    'name' => $g->name,
                    'icon_url' => $g->icon ? "https://cdn.discordapp.com/icons/{$g->guild_id}/{$g->icon}.png" : null,
                    'owner' => $g->owner,
                ];
            });

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
        $botToken = env('DISCORD_BOT_TOKEN');
        
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

    private function fetchGuildCategories($guildId)
    {
        $botToken = env('DISCORD_BOT_TOKEN');
        
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

    private function checkBotOnGuild($guildId, $botClientId, $botToken)
    {
        if (!$botToken) {
            \Log::warning("Bot Token nicht verfügbar für Guild-Prüfung");
            return false;
        }

        try {
            // Methode 1: Prüfe über Bot-Member-Liste (am zuverlässigsten)
            $memberResponse = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->timeout(5)->get("https://discord.com/api/v10/guilds/{$guildId}/members/{$botClientId}");
            
            if ($memberResponse->successful()) {
                \Log::debug("Bot gefunden auf Guild {$guildId} via Member-API");
                return true;
            }
            
            // Methode 2: Prüfe über Guild-Informationen
            $guildResponse = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->timeout(5)->get("https://discord.com/api/v10/guilds/{$guildId}");
            
            if ($guildResponse->successful()) {
                \Log::debug("Bot gefunden auf Guild {$guildId} via Guild-API");
                return true;
            }
            
            \Log::debug("Bot NICHT gefunden auf Guild {$guildId} - Status: {$memberResponse->status()}");
            return false;
        } catch (\Exception $e) {
            // Bei Fehler (z.B. Bot nicht auf Server, Timeout), return false
            \Log::warning("Bot check failed for guild {$guildId}: " . $e->getMessage());
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

        $allGuilds = UserGuild::where('user_id', $user->id)
            ->where('bot_joined', true)
            ->orderBy('name')
            ->get()
            ->map(function ($g) {
                return [
                    'id' => $g->guild_id,
                    'name' => $g->name,
                    'icon_url' => $g->icon ? "https://cdn.discordapp.com/icons/{$g->guild_id}/{$g->icon}.png" : null,
                    'owner' => $g->owner,
                ];
            });

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
}

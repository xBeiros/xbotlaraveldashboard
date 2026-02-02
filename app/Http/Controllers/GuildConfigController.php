<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Guild;
use App\Models\WelcomeConfig;
use App\Models\GoodbyeConfig;
use App\Models\UserGuild;
use App\Models\SocialNotification;
use App\Models\AutoModerationConfig;
use App\Models\LevelingConfig;
use App\Models\RoleReward;
use App\Models\RankCardConfig;
use App\Models\ReactionRole;
use App\Models\TicketCategory;
use App\Models\TicketPost;
use App\Models\Ticket;
use App\Models\AutoDeleteMessage;
use App\Models\Giveaway;
use App\Models\Birthday;
use App\Models\AddOn;
use App\Models\TeamRank;
use App\Models\TeamMember;
use App\Models\TeamManagementConfig;
use App\Models\TeamAnnouncementTemplate;
use App\Models\FactionManagementConfig;
use App\Models\SavedEmbed;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuildConfigController extends BaseGuildController
{
    /**
     * Prüft Zugriff und gibt UserGuild und GuildModel zurück
     * 
     * @param string $guild Discord Guild ID
     * @return array ['userGuild' => UserGuild, 'guildModel' => Guild]|RedirectResponse
     */
    private function authorizeAndGetGuild($guild)
    {
        $access = $this->checkGuildAccess($guild);
        if ($access['error']) {
            return $access['error'];
        }

        $userGuild = $access['userGuild'];
        
        if (!$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
        return ['userGuild' => $userGuild, 'guildModel' => $guildModel];
    }

    public function updateWelcome(Request $request, $guild)
    {
        $result = $this->authorizeAndGetGuild($guild);
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }

        $guildModel = $result['guildModel'];
        
        // Input Validation
        $validated = $request->validate([
            'enabled' => 'boolean',
            'channel_id' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:2000',
            'use_embed' => 'boolean',
            'embed_author' => 'boolean',
            'embed_title' => 'nullable|string|max:256',
            'embed_description' => 'nullable|string|max:4096',
            'embed_color' => 'nullable|string|max:7',
            'embed_thumbnail' => 'nullable|url|max:500',
            'embed_image' => 'nullable|url|max:500',
            'embed_footer' => 'boolean',
            'use_welcome_card' => 'boolean',
            'card_font' => 'nullable|string|max:100',
            'card_text_color' => 'nullable|string|max:7',
            'card_background_color' => 'nullable|string|max:7',
            'card_overlay_opacity' => 'nullable|integer|min:0|max:100',
            'card_background_image' => 'nullable|url|max:500',
            'card_title' => 'nullable|string|max:256',
            'card_avatar_position' => 'nullable|string|in:top,bottom,left,right',
        ]);

        $guildModel->welcomeConfig()->updateOrCreate(
            ['guild_id' => $guildModel->id],
            [
                'enabled' => $validated['enabled'] ?? false,
                'channel_id' => $validated['channel_id'] ?? null,
                'message' => $validated['message'] ?? null,
                'use_embed' => $validated['use_embed'] ?? false,
                'embed_author' => $validated['embed_author'] ?? true,
                'embed_title' => $validated['embed_title'] ?? null,
                'embed_description' => $validated['embed_description'] ?? null,
                'embed_color' => $validated['embed_color'] ?? null,
                'embed_thumbnail' => $validated['embed_thumbnail'] ?? null,
                'embed_image' => $validated['embed_image'] ?? null,
                'embed_footer' => $validated['embed_footer'] ?? true,
                'use_welcome_card' => $validated['use_welcome_card'] ?? false,
                'card_font' => $validated['card_font'] ?? null,
                'card_text_color' => $validated['card_text_color'] ?? null,
                'card_background_color' => $validated['card_background_color'] ?? null,
                'card_overlay_opacity' => $validated['card_overlay_opacity'] ?? 50,
                'card_background_image' => $validated['card_background_image'] ?? null,
                'card_title' => $validated['card_title'] ?? null,
                'card_avatar_position' => $validated['card_avatar_position'] ?? 'top',
            ]
        );

        return back()->with('success', 'Willkommensgrüße erfolgreich gespeichert!');
    }

    public function updateGoodbye(Request $request, $guild)
    {
        $result = $this->authorizeAndGetGuild($guild);
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }

        $guildModel = $result['guildModel'];
        
        $guildModel->goodbyeConfig()->updateOrCreate(
            ['guild_id' => $guildModel->id],
            [
                'enabled' => $request->enabled ?? false,
                'channel_id' => $request->channel_id,
                'message' => $request->message,
                'use_embed' => $request->use_embed ?? false,
                'embed_author' => $request->embed_author ?? true,
                'embed_title' => $request->embed_title,
                'embed_description' => $request->embed_description,
                'embed_color' => $request->embed_color,
                'embed_thumbnail' => $request->embed_thumbnail,
                'embed_image' => $request->embed_image,
                'embed_footer' => $request->embed_footer ?? true,
                'use_goodbye_card' => $request->use_goodbye_card ?? false,
                'card_font' => $request->card_font,
                'card_text_color' => $request->card_text_color,
                'card_background_color' => $request->card_background_color,
                'card_overlay_opacity' => $request->card_overlay_opacity ?? 50,
                'card_background_image' => $request->card_background_image,
                'card_title' => $request->card_title,
                'card_avatar_position' => $request->card_avatar_position ?? 'top',
            ]
        );

        return back()->with('success', 'Abschiedsgrüße erfolgreich gespeichert!');
    }

    public function storeSocial(Request $request, $guild)
    {
        $result = $this->authorizeAndGetGuild($guild);
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }

        $guildModel = $result['guildModel'];
        
        $guildModel->socialNotifications()->create([
            'platform' => $request->platform,
            'channel_id' => $request->channel_id,
            'username' => $request->username,
            'webhook_url' => $request->webhook_url,
            'enabled' => $request->enabled ?? false,
            'notify_live' => $request->notify_live ?? true,
            'custom_message' => $request->custom_message,
        ]);

        return back()->with('success', 'Social Media Benachrichtigung erfolgreich erstellt!');
    }

    public function updateSocial(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $notification = $guildModel->socialNotifications()->findOrFail($id);
        
        $notification->update([
            'channel_id' => $request->channel_id,
            'username' => $request->username,
            'webhook_url' => $request->webhook_url,
            'enabled' => $request->enabled ?? false,
            'notify_live' => $request->notify_live ?? true,
            'custom_message' => $request->custom_message,
        ]);

        return back()->with('success', 'Social Media Benachrichtigung erfolgreich aktualisiert!');
    }

    public function deleteSocial($guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $notification = $guildModel->socialNotifications()->findOrFail($id);
        $notification->delete();

        return back()->with('success', 'Social Media Benachrichtigung erfolgreich gelöscht!');
    }

    public function updateAutoModeration(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
        $guildModel->autoModerationConfig()->updateOrCreate(
            ['guild_id' => $guildModel->id],
            [
                'bad_words_enabled' => $request->bad_words_enabled ?? false,
                'bad_words_list' => $request->bad_words_list ?? [],
                'bad_words_delete_message' => $request->bad_words_delete_message ?? true,
                'bad_words_warning_message' => $request->bad_words_warning_message,
                'bad_words_use_embed' => $request->bad_words_use_embed ?? false,
                'bad_words_embed_title' => $request->bad_words_embed_title,
                'bad_words_embed_description' => $request->bad_words_embed_description,
                'bad_words_embed_color' => $request->bad_words_embed_color ?? '#ff0000',
                'bad_words_embed_footer' => $request->bad_words_embed_footer ?? true,
                'block_discord_invites' => $request->block_discord_invites ?? false,
                'block_discord_invites_delete_message' => $request->block_discord_invites_delete_message ?? true,
                'block_discord_invites_warning_message' => $request->block_discord_invites_warning_message,
                'block_discord_invites_use_embed' => $request->block_discord_invites_use_embed ?? false,
                'block_discord_invites_embed_title' => $request->block_discord_invites_embed_title,
                'block_discord_invites_embed_description' => $request->block_discord_invites_embed_description,
                'block_discord_invites_embed_color' => $request->block_discord_invites_embed_color ?? '#ff0000',
                'block_discord_invites_embed_footer' => $request->block_discord_invites_embed_footer ?? true,
                'whitelist_channels' => $request->whitelist_channels ?? [],
                'whitelist_roles' => $request->whitelist_roles ?? [],
            ]
        );

        return back()->with('success', 'Auto-Moderation erfolgreich gespeichert!');
    }

    public function updateLeveling(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
        $guildModel->levelingConfig()->updateOrCreate(
            ['guild_id' => $guildModel->id],
            [
                'enabled' => $request->enabled ?? false,
                'xp_rate' => $request->xp_rate ?? 1.00,
                'min_xp' => $request->min_xp ?? 15,
                'max_xp' => $request->max_xp ?? 25,
                'cooldown_seconds' => $request->cooldown_seconds ?? 60,
                'level_up_channel_id' => $request->level_up_channel_id,
                'level_up_type' => $request->level_up_type ?? 'current_channel',
                'level_up_message' => $request->level_up_message,
                'role_reward_type' => $request->role_reward_type ?? 'stack',
                'remove_role_on_xp_loss' => $request->remove_role_on_xp_loss ?? false,
                'excluded_roles_type' => $request->excluded_roles_type ?? 'allow_all_except',
                'excluded_roles' => $request->excluded_roles ?? [],
                'excluded_channels_type' => $request->excluded_channels_type ?? 'allow_all_except',
                'excluded_channels' => $request->excluded_channels ?? [],
            ]
        );

        return back()->with('success', 'Leveling-Konfiguration erfolgreich gespeichert!');
    }

    public function storeRoleReward(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
        RoleReward::updateOrCreate(
            [
                'guild_id' => $guildModel->id,
                'level' => $request->level,
            ],
            [
                'role_id' => $request->role_id,
            ]
        );

        return back()->with('success', 'Rollenbelohnung erfolgreich hinzugefügt!');
    }

    public function deleteRoleReward($guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        RoleReward::findOrFail($id)->delete();

        return back()->with('success', 'Rollenbelohnung erfolgreich gelöscht!');
    }

    public function updateRankCard(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
        $guildModel->rankCardConfig()->updateOrCreate(
            ['guild_id' => $guildModel->id],
            [
                'background_type' => $request->background_type ?? 'color',
                'background_color' => $request->background_color ?? '#000000',
                'background_image' => $request->background_image,
                'custom_background_url' => $request->custom_background_url,
                'overlay_opacity' => $request->overlay_opacity ?? 0,
                'text_color' => $request->text_color ?? '#ffffff',
                'rank_text_color' => $request->rank_text_color ?? '#ffffff',
                'level_text_color' => $request->level_text_color ?? '#5865f2',
                'xp_text_color' => $request->xp_text_color ?? '#ffffff',
                'progress_bar_color' => $request->progress_bar_color ?? '#5865f2',
                'welcome_message' => $request->welcome_message,
            ]
        );

        return back()->with('success', 'Rangkarte erfolgreich gespeichert!');
    }

    public function storeReactionRole(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();

        $reactionRole = $guildModel->reactionRoles()->create([
            'channel_id' => $request->channel_id,
            'enabled' => $request->enabled ?? true,
            'embed_title' => $request->embed_title,
            'embed_description' => $request->embed_description,
            'embed_color' => $request->embed_color ?? '#5865f2',
            'embed_thumbnail' => $request->embed_thumbnail,
            'embed_image' => $request->embed_image,
            'embed_banner' => $request->embed_banner,
            'embed_footer' => $request->embed_footer ?? true,
            'reactions' => $request->reactions ?? [],
        ]);

        // Automatically send the message when creating
        if ($request->channel_id && !empty($request->reactions)) {
            try {
                $this->sendReactionRoleMessage($reactionRole, $guildModel);
            } catch (\Exception $e) {
                \Log::error('Fehler beim automatischen Senden der Nachricht: ' . $e->getMessage());
                // Don't fail the creation, just log the error
            }
        }

        return back()->with('success', 'Reaktionsrolle erfolgreich erstellt!');
    }

    public function updateReactionRole(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $reactionRole = $guildModel->reactionRoles()->findOrFail($id);

        $reactionRole->update([
            'channel_id' => $request->channel_id,
            'enabled' => $request->enabled ?? true,
            'embed_title' => $request->embed_title,
            'embed_description' => $request->embed_description,
            'embed_color' => $request->embed_color ?? '#5865f2',
            'embed_thumbnail' => $request->embed_thumbnail,
            'embed_image' => $request->embed_image,
            'embed_banner' => $request->embed_banner,
            'embed_footer' => $request->embed_footer ?? true,
            'reactions' => $request->reactions ?? [],
        ]);

        return back()->with('success', 'Reaktionsrolle erfolgreich aktualisiert!');
    }

    public function deleteReactionRole($guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $reactionRole = $guildModel->reactionRoles()->findOrFail($id);
        $reactionRole->delete();

        return back()->with('success', 'Reaktionsrolle erfolgreich gelöscht!');
    }

    public function resendReactionRole($guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $reactionRole = $guildModel->reactionRoles()->findOrFail($id);

        if (!$reactionRole->channel_id) {
            return back()->with('error', 'Kein Kanal ausgewählt!');
        }

        $botToken = config('services.discord.bot_token');
        if (!$botToken) {
            return back()->with('error', 'Bot Token nicht konfiguriert!');
        }

        try {
            $this->sendReactionRoleMessage($reactionRole, $guildModel);
            return back()->with('success', 'Nachricht erfolgreich gesendet!');
        } catch (\Exception $e) {
            \Log::error('Fehler beim Senden der Reaktionsrollen-Nachricht: ' . $e->getMessage());
            return back()->with('error', 'Fehler beim Senden der Nachricht: ' . $e->getMessage());
        }
    }

    private function sendReactionRoleMessage($reactionRole, $guildModel)
    {
        $botToken = config('services.discord.bot_token');
        if (!$botToken) {
            throw new \Exception('Bot Token nicht konfiguriert!');
        }

        // Build embed
        $embed = [];
        
        if ($reactionRole->embed_title) {
            $embed['title'] = $reactionRole->embed_title;
        }
        
        if ($reactionRole->embed_description) {
            $embed['description'] = $reactionRole->embed_description;
        }
        
        if ($reactionRole->embed_color) {
            // Convert hex color to integer
            $color = str_replace('#', '', $reactionRole->embed_color);
            $embed['color'] = hexdec($color);
        }
        
        if ($reactionRole->embed_thumbnail) {
            $embed['thumbnail'] = ['url' => $reactionRole->embed_thumbnail];
        }
        
        if ($reactionRole->embed_image) {
            $embed['image'] = ['url' => $reactionRole->embed_image];
        }
        
        // Banner (Discord doesn't support banner in embeds, but we can use it as image if no image is set)
        if ($reactionRole->embed_banner && !$reactionRole->embed_image) {
            $embed['image'] = ['url' => $reactionRole->embed_banner];
        }
        
        if ($reactionRole->embed_footer) {
            $guildName = $guildModel->name;
            $embed['footer'] = [
                'text' => $guildName . ' • ' . now()->format('d.m.Y H:i'),
            ];
        }

        // Build message payload
        $payload = [];
        if (!empty($embed)) {
            $payload['embeds'] = [$embed];
        }

        // Send message
        $response = Http::withHeaders([
            'Authorization' => 'Bot ' . $botToken,
            'Content-Type' => 'application/json',
        ])->timeout(10)->post(
            "https://discord.com/api/v10/channels/{$reactionRole->channel_id}/messages",
            $payload
        );

        if (!$response->successful()) {
            $errorBody = $response->json();
            $errorMessage = $errorBody['message'] ?? 'Unbekannter Fehler';
            \Log::error('Discord API Error:', [
                'status' => $response->status(),
                'error' => $errorMessage,
            ]);
            throw new \Exception('Fehler beim Senden der Nachricht: ' . $errorMessage);
        }

        $messageData = $response->json();
        $messageId = $messageData['id'];

        // Update message_id in database
        $reactionRole->update(['message_id' => $messageId]);

        // Wait a bit for Discord to process the message before adding reactions
        usleep(500000); // 0.5 seconds delay

        // Add reactions to the message
        $reactions = $reactionRole->reactions ?? [];
        \Log::info('Füge Reaktionen hinzu:', ['count' => count($reactions), 'reactions' => $reactions]);
        
        if (empty($reactions)) {
            \Log::warning('Keine Reaktionen zum Hinzufügen gefunden');
            return;
        }
        
        foreach ($reactions as $index => $reaction) {
            // Check if reaction is an array and has emoji field
            if (!is_array($reaction)) {
                \Log::warning('Reaktion ist kein Array:', ['reaction' => $reaction]);
                continue;
            }
            
            if (empty($reaction['emoji'])) {
                \Log::warning('Reaktion ohne Emoji übersprungen:', ['reaction' => $reaction]);
                continue;
            }
            
            $emoji = trim($reaction['emoji']);
            
            // Load Discord emoji mapping from JSON file
            $emojiJsonPath = storage_path('app/discord-emojis.json');
            $emojiNameMap = [];
            if (file_exists($emojiJsonPath)) {
                $emojiJson = json_decode(file_get_contents($emojiJsonPath), true);
                if ($emojiJson) {
                    // Convert to format with colons: :name: => emoji
                    foreach ($emojiJson as $name => $unicode) {
                        $emojiNameMap[':' . $name . ':'] = $unicode;
                    }
                }
            }
            
            // Fallback to hardcoded map if JSON file doesn't exist (shouldn't happen)
            if (empty($emojiNameMap)) {
                $emojiNameMap = [
                    ':white_check_mark:' => '✅',
                ':x:' => '❌',
                ':heavy_check_mark:' => '✔️',
                ':heavy_multiplication_x:' => '✖️',
                ':thumbsup:' => '👍',
                ':thumbsdown:' => '👎',
                ':heart:' => '❤️',
                ':star:' => '⭐',
                ':star2:' => '🌟',
                ':fire:' => '🔥',
                ':tada:' => '🎉',
                ':confetti_ball:' => '🎊',
                ':balloon:' => '🎈',
                ':gift:' => '🎁',
                ':trophy:' => '🏆',
                ':medal:' => '🏅',
                ':1st_place_medal:' => '🥇',
                ':2nd_place_medal:' => '🥈',
                ':3rd_place_medal:' => '🥉',
                ':warning:' => '⚠️',
                ':no_entry:' => '⛔',
                ':stop_sign:' => '🛑',
                ':arrow_up:' => '⬆️',
                ':arrow_down:' => '⬇️',
                ':arrow_left:' => '⬅️',
                ':arrow_right:' => '➡️',
                ':arrow_forward:' => '▶️',
                ':arrow_backward:' => '◀️',
                ':play_pause:' => '⏯️',
                ':play_or_pause_button:' => '⏯️',
                ':pause_button:' => '⏸️',
                ':stop_button:' => '⏹️',
                ':next_track_button:' => '⏭️',
                ':previous_track_button:' => '⏮️',
                ':fast_forward:' => '⏩',
                ':rewind:' => '⏪',
                ':twisted_rightwards_arrows:' => '🔀',
                ':repeat:' => '🔁',
                ':repeat_one:' => '🔂',
                ':shuffle:' => '🔀',
                ':musical_note:' => '🎵',
                ':notes:' => '🎶',
                ':microphone:' => '🎤',
                ':headphones:' => '🎧',
                ':radio:' => '📻',
                ':loud_sound:' => '🔊',
                ':sound:' => '🔉',
                ':speaker:' => '🔈',
                ':mute:' => '🔇',
                ':bell:' => '🔔',
                ':no_bell:' => '🔕',
                ':mega:' => '📣',
                ':speaking_head:' => '🗣️',
                ':thought_balloon:' => '💭',
                ':right_anger_bubble:' => '🗯️',
                ':speech_balloon:' => '💬',
                ':left_speech_bubble:' => '🗨️',
                ':clock1:' => '🕐',
                ':clock2:' => '🕑',
                ':clock3:' => '🕒',
                ':clock4:' => '🕓',
                ':clock5:' => '🕔',
                ':clock6:' => '🕕',
                ':clock7:' => '🕖',
                ':clock8:' => '🕗',
                ':clock9:' => '🕘',
                ':clock10:' => '🕙',
                ':clock11:' => '🕚',
                ':clock12:' => '🕛',
                ':zero:' => '0️⃣',
                ':one:' => '1️⃣',
                ':two:' => '2️⃣',
                ':three:' => '3️⃣',
                ':four:' => '4️⃣',
                ':five:' => '5️⃣',
                ':six:' => '6️⃣',
                ':seven:' => '7️⃣',
                ':eight:' => '8️⃣',
                ':nine:' => '9️⃣',
                ':keycap_ten:' => '🔟',
                ':hash:' => '#️⃣',
                ':asterisk:' => '*️⃣',
                ':arrow_up_small:' => '🔺',
                ':arrow_down_small:' => '🔻',
                ':arrow_heading_up:' => '⤴️',
                ':arrow_heading_down:' => '⤵️',
                ':left_right_arrow:' => '↔️',
                ':arrow_up_down:' => '↕️',
                ':arrows_counterclockwise:' => '🔄',
                ':arrow_right_hook:' => '↪️',
                ':leftwards_arrow_with_hook:' => '↩️',
                ':arrow_upper_right:' => '↗️',
                ':arrow_upper_left:' => '↖️',
                ':arrow_lower_right:' => '↘️',
                ':arrow_lower_left:' => '↙️',
                ':arrow_up_down:' => '↕️',
                ':left_right_arrow:' => '↔️',
                ':arrows_clockwise:' => '🔃',
                ':arrows_counterclockwise:' => '🔄',
                ':back:' => '🔙',
                ':end:' => '🔚',
                ':on:' => '🔛',
                ':soon:' => '🔜',
                ':top:' => '🔝',
                ':place_of_worship:' => '🛐',
                ':atom_symbol:' => '⚛️',
                ':om:' => '🕉️',
                ':star_of_david:' => '✡️',
                ':wheel_of_dharma:' => '☸️',
                ':yin_yang:' => '☯️',
                ':latin_cross:' => '✝️',
                ':orthodox_cross:' => '☦️',
                ':star_and_crescent:' => '☪️',
                ':peace_symbol:' => '☮️',
                ':menorah:' => '🕎',
                ':six_pointed_star:' => '🔯',
                ':aries:' => '♈',
                ':taurus:' => '♉',
                ':gemini:' => '♊',
                ':cancer:' => '♋',
                ':leo:' => '♌',
                ':virgo:' => '♍',
                ':libra:' => '♎',
                ':scorpius:' => '♏',
                ':sagittarius:' => '♐',
                ':capricorn:' => '♑',
                ':aquarius:' => '♒',
                ':pisces:' => '♓',
                ':ophiuchus:' => '⛎',
                ':twisted_rightwards_arrows:' => '🔀',
                ':repeat:' => '🔁',
                ':repeat_one:' => '🔂',
                ':arrow_forward:' => '▶️',
                ':fast_forward:' => '⏩',
                ':next_track_button:' => '⏭️',
                ':play_or_pause_button:' => '⏯️',
                ':arrow_backward:' => '◀️',
                ':rewind:' => '⏪',
                ':previous_track_button:' => '⏮️',
                ':arrow_up_small:' => '🔺',
                ':arrow_double_up:' => '⏫',
                ':arrow_down_small:' => '🔻',
                ':arrow_double_down:' => '⏬',
                ':arrow_right:' => '➡️',
                ':arrow_left:' => '⬅️',
                ':arrow_up:' => '⬆️',
                ':arrow_down:' => '⬇️',
                ':arrow_upper_right:' => '↗️',
                ':arrow_lower_right:' => '↘️',
                ':arrow_lower_left:' => '↙️',
                ':arrow_upper_left:' => '↖️',
                ':arrow_up_down:' => '↕️',
                ':left_right_arrow:' => '↔️',
                ':arrows_counterclockwise:' => '🔄',
                ':arrow_right_hook:' => '↪️',
                ':leftwards_arrow_with_hook:' => '↩️',
                ':arrow_heading_up:' => '⤴️',
                ':arrow_heading_down:' => '⤵️',
                ':twisted_rightwards_arrows:' => '🔀',
                ':repeat:' => '🔁',
                ':repeat_one:' => '🔂',
                ':arrow_forward:' => '▶️',
                ':fast_forward:' => '⏩',
                ':next_track_button:' => '⏭️',
                ':play_or_pause_button:' => '⏯️',
                ':arrow_backward:' => '◀️',
                ':rewind:' => '⏪',
                ':previous_track_button:' => '⏮️',
                ':arrow_up_small:' => '🔺',
                ':arrow_double_up:' => '⏫',
                ':arrow_down_small:' => '🔻',
                ':arrow_double_down:' => '⏬',
                ':arrow_right:' => '➡️',
                ':arrow_left:' => '⬅️',
                ':arrow_up:' => '⬆️',
                ':arrow_down:' => '⬇️',
                ':arrow_upper_right:' => '↗️',
                ':arrow_lower_right:' => '↘️',
                ':arrow_lower_left:' => '↙️',
                ':arrow_upper_left:' => '↖️',
                ':arrow_up_down:' => '↕️',
                ':left_right_arrow:' => '↔️',
                ':arrows_counterclockwise:' => '🔄',
                ':arrow_right_hook:' => '↪️',
                ':leftwards_arrow_with_hook:' => '↩️',
                ':arrow_heading_up:' => '⤴️',
                ':arrow_heading_down:' => '⤵️',
                // Flag emojis
                ':flag_gb:' => '🇬🇧',
                ':flag_de:' => '🇩🇪',
                ':flag_us:' => '🇺🇸',
                ':flag_fr:' => '🇫🇷',
                ':flag_es:' => '🇪🇸',
                ':flag_it:' => '🇮🇹',
                ':flag_nl:' => '🇳🇱',
                ':flag_pl:' => '🇵🇱',
                ':flag_ru:' => '🇷🇺',
                ':flag_cn:' => '🇨🇳',
                ':flag_jp:' => '🇯🇵',
                ':flag_kr:' => '🇰🇷',
                ':flag_br:' => '🇧🇷',
                ':flag_ca:' => '🇨🇦',
                ':flag_au:' => '🇦🇺',
                ':flag_in:' => '🇮🇳',
                ':flag_mx:' => '🇲🇽',
                ':flag_se:' => '🇸🇪',
                ':flag_no:' => '🇳🇴',
                ':flag_dk:' => '🇩🇰',
                ':flag_fi:' => '🇫🇮',
                ':flag_ch:' => '🇨🇭',
                ':flag_at:' => '🇦🇹',
                ':flag_be:' => '🇧🇪',
                ':flag_cz:' => '🇨🇿',
                ':flag_ie:' => '🇮🇪',
                ':flag_nz:' => '🇳🇿',
                ':flag_sg:' => '🇸🇬',
                ':flag_th:' => '🇹🇭',
                ':flag_vn:' => '🇻🇳',
                ':flag_id:' => '🇮🇩',
                ':flag_ph:' => '🇵🇭',
                ':flag_my:' => '🇲🇾',
                ':flag_tr:' => '🇹🇷',
                ':flag_gr:' => '🇬🇷',
                ':flag_pt:' => '🇵🇹',
                ':flag_ro:' => '🇷🇴',
                ':flag_hu:' => '🇭🇺',
                ':flag_bg:' => '🇧🇬',
                ':flag_hr:' => '🇭🇷',
                ':flag_sk:' => '🇸🇰',
                ':flag_si:' => '🇸🇮',
                ':flag_ee:' => '🇪🇪',
                ':flag_lv:' => '🇱🇻',
                ':flag_lt:' => '🇱🇹',
                ':flag_ua:' => '🇺🇦',
                ':flag_by:' => '🇧🇾',
                ':flag_rs:' => '🇷🇸',
                ':flag_ba:' => '🇧🇦',
                ':flag_mk:' => '🇲🇰',
                ':flag_al:' => '🇦🇱',
                ':flag_me:' => '🇲🇪',
                ':flag_is:' => '🇮🇸',
                ':flag_lu:' => '🇱🇺',
                ':flag_mt:' => '🇲🇹',
                ':flag_cy:' => '🇨🇾',
                ':flag_li:' => '🇱🇮',
                ':flag_mc:' => '🇲🇨',
                ':flag_sm:' => '🇸🇲',
                ':flag_va:' => '🇻🇦',
                ':flag_ad:' => '🇦🇩',
                ':flag_il:' => '🇮🇱',
                ':flag_sa:' => '🇸🇦',
                ':flag_ae:' => '🇦🇪',
                ':flag_eg:' => '🇪🇬',
                ':flag_za:' => '🇿🇦',
                ':flag_ng:' => '🇳🇬',
                ':flag_ke:' => '🇰🇪',
                ':flag_et:' => '🇪🇹',
                ':flag_gh:' => '🇬🇭',
                ':flag_tz:' => '🇹🇿',
                ':flag_ug:' => '🇺🇬',
                ':flag_rw:' => '🇷🇼',
                ':flag_zm:' => '🇿🇲',
                ':flag_mw:' => '🇲🇼',
                ':flag_mz:' => '🇲🇿',
                ':flag_ao:' => '🇦🇴',
                ':flag_cm:' => '🇨🇲',
                ':flag_sn:' => '🇸🇳',
                ':flag_ci:' => '🇨🇮',
                ':flag_ma:' => '🇲🇦',
                ':flag_dz:' => '🇩🇿',
                ':flag_tn:' => '🇹🇳',
                ':flag_ly:' => '🇱🇾',
                ':flag_sd:' => '🇸🇩',
                ':flag_so:' => '🇸🇴',
                ':flag_dj:' => '🇩🇯',
                ':flag_er:' => '🇪🇷',
                ':flag_ss:' => '🇸🇸',
                ':flag_cf:' => '🇨🇫',
                ':flag_td:' => '🇹🇩',
                ':flag_ne:' => '🇳🇪',
                ':flag_ml:' => '🇲🇱',
                ':flag_bf:' => '🇧🇫',
                ':flag_mr:' => '🇲🇷',
                ':flag_gm:' => '🇬🇲',
                ':flag_gw:' => '🇬🇼',
                ':flag_gn:' => '🇬🇳',
                ':flag_sl:' => '🇸🇱',
                ':flag_lr:' => '🇱🇷',
                ':flag_cv:' => '🇨🇻',
                ':flag_st:' => '🇸🇹',
                ':flag_gq:' => '🇬🇶',
                ':flag_ga:' => '🇬🇦',
                ':flag_cg:' => '🇨🇬',
                ':flag_cd:' => '🇨🇩',
                ':flag_bi:' => '🇧🇮',
                ':flag_rw:' => '🇷🇼',
                ':flag_ug:' => '🇺🇬',
                ':flag_tz:' => '🇹🇿',
                ':flag_ke:' => '🇰🇪',
                ':flag_et:' => '🇪🇹',
                ':flag_dj:' => '🇩🇯',
                ':flag_so:' => '🇸🇴',
                ':flag_er:' => '🇪🇷',
                ':flag_ss:' => '🇸🇸',
                ':flag_cf:' => '🇨🇫',
                ':flag_td:' => '🇹🇩',
                ':flag_ne:' => '🇳🇪',
                ':flag_ml:' => '🇲🇱',
                ':flag_bf:' => '🇧🇫',
                ':flag_mr:' => '🇲🇷',
                ':flag_gm:' => '🇬🇲',
                ':flag_gw:' => '🇬🇼',
                ':flag_gn:' => '🇬🇳',
                ':flag_sl:' => '🇸🇱',
                ':flag_lr:' => '🇱🇷',
                ':flag_cv:' => '🇨🇻',
                ':flag_st:' => '🇸🇹',
                ':flag_gq:' => '🇬🇶',
                ':flag_ga:' => '🇬🇦',
                ':flag_cg:' => '🇨🇬',
                ':flag_cd:' => '🇨🇩',
                ':flag_bi:' => '🇧🇮',
                ':flag_rw:' => '🇷🇼',
                ':flag_ug:' => '🇺🇬',
                ':flag_tz:' => '🇹🇿',
                ':flag_ke:' => '🇰🇪',
                ':flag_et:' => '🇪🇹',
                ':flag_dj:' => '🇩🇯',
                ':flag_so:' => '🇸🇴',
                ':flag_er:' => '🇪🇷',
                ':flag_ss:' => '🇸🇸',
                ':flag_cf:' => '🇨🇫',
                ':flag_td:' => '🇹🇩',
                ':flag_ne:' => '🇳🇪',
                ':flag_ml:' => '🇲🇱',
                ':flag_bf:' => '🇧🇫',
                ':flag_mr:' => '🇲🇷',
                ':flag_gm:' => '🇬🇲',
                ':flag_gw:' => '🇬🇼',
                ':flag_gn:' => '🇬🇳',
                ':flag_sl:' => '🇸🇱',
                ':flag_lr:' => '🇱🇷',
                ':flag_cv:' => '🇨🇻',
                ':flag_st:' => '🇸🇹',
                ':flag_gq:' => '🇬🇶',
                ':flag_ga:' => '🇬🇦',
                ':flag_cg:' => '🇨🇬',
                ':flag_cd:' => '🇨🇩',
                ':flag_bi:' => '🇧🇮',
                ];
            }
            
            // Handle custom emojis (<:name:id> or <a:name:id> for animated)
            if (preg_match('/<a?:([^:]+):(\d+)>/', $emoji, $matches)) {
                // Custom emoji (animated or static)
                $emojiName = $matches[1];
                $emojiId = $matches[2];
                // For custom emojis, Discord API expects: name:id (URL encode only the name part)
                $emojiParam = rawurlencode($emojiName) . ':' . $emojiId;
            } 
            // Handle Discord emoji names (like :white_check_mark: or :flag_gb:)
            elseif (preg_match('/^:([^:]+):$/', $emoji, $nameMatches)) {
                // Discord emoji name - use directly without conversion
                // Discord API accepts emoji names like :white_check_mark: directly
                // Just remove the colons and URL encode
                $emojiName = $nameMatches[1];
                $emojiParam = rawurlencode($emojiName);
                
                // Optional: Try to convert known flag emojis to Unicode for better compatibility
                if (isset($emojiNameMap[$emoji])) {
                    $unicodeEmoji = $emojiNameMap[$emoji];
                    // Use Unicode version if available, otherwise use the name
                    $emojiParam = rawurlencode($unicodeEmoji);
                    \Log::info('Emoji-Name zu Unicode konvertiert:', ['from' => $emoji, 'to' => $unicodeEmoji]);
                } else {
                    // Use the emoji name directly (Discord should handle it)
                    $emojiParam = rawurlencode($emojiName);
                    \Log::info('Emoji-Name direkt verwendet:', ['emoji' => $emoji, 'param' => $emojiParam]);
                }
            } 
            // Unicode emoji (direct emoji character)
            else {
                // Unicode emoji - Discord API expects it URL encoded in the path
                // Discord requires unicode emojis to be properly URL encoded
                // Use rawurlencode which handles UTF-8 characters correctly
                $emojiParam = rawurlencode($emoji);
            }

                try {
                    // Add small delay between reactions to avoid rate limits
                    if ($index > 0) {
                        usleep(250000); // 0.25 seconds between reactions
                    }

                    // Build the reaction URL
                    // Discord API: PUT /channels/{channel.id}/messages/{message.id}/reactions/{emoji}/@me
                    // For unicode: emoji must be URL encoded
                    // For custom: emoji must be name:id format (name URL encoded)
                    $reactionUrl = "https://discord.com/api/v10/channels/{$reactionRole->channel_id}/messages/{$messageId}/reactions/{$emojiParam}/@me";
                    
                    \Log::info('Sende Reaktion:', [
                        'emoji' => $emoji,
                        'emojiParam' => $emojiParam,
                        'url' => $reactionUrl,
                        'isCustom' => preg_match('/<a?:([^:]+):(\d+)>/', $emoji) ? true : false,
                    ]);

                    // Use PUT method to add reaction
                    // Discord API expects the emoji in the URL path, properly encoded
                    // Important: PUT request should have empty body for reactions
                    $reactionResponse = Http::withHeaders([
                        'Authorization' => 'Bot ' . $botToken,
                    ])->withBody('', 'application/json')->timeout(5)->put($reactionUrl);
                    
                    if (!$reactionResponse->successful()) {
                        \Log::error('Fehler beim Hinzufügen der Reaktion:', [
                            'emoji' => $emoji,
                            'emojiParam' => $emojiParam,
                            'status' => $reactionResponse->status(),
                            'body' => $reactionResponse->body(),
                            'response' => $reactionResponse->json(),
                        ]);
                    } else {
                        \Log::info('Reaktion erfolgreich hinzugefügt:', [
                            'emoji' => $emoji,
                            'emojiParam' => $emojiParam,
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Exception beim Hinzufügen der Reaktion:', [
                        'emoji' => $emoji,
                        'emojiParam' => $emojiParam,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
        }
    }

    public function updateServerManagement(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();

        $validated = $request->validate([
            'language' => 'required|in:de,en,tr',
            'timezone' => 'required|string|max:50',
        ]);

        $guildModel->update([
            'language' => $validated['language'],
            'timezone' => $validated['timezone'],
        ]);

        return back()->with('success', 'Server-Einstellungen erfolgreich gespeichert!');
    }

    public function updateStatisticsChannel(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();

        $validated = $request->validate([
            'statistics_enabled' => 'nullable|boolean',
            'statistics_channel_name' => 'nullable|string|max:100',
            'statistics_category_id' => 'nullable|string|max:32',
            'statistics_channel_name_members' => 'nullable|string|max:100',
            'statistics_channel_name_joins' => 'nullable|string|max:100',
            'statistics_channel_name_leaves' => 'nullable|string|max:100',
            'statistics_channel_name_vc' => 'nullable|string|max:100',
            'statistics_channel_name_boosts' => 'nullable|string|max:100',
            'statistics_stat_members' => 'nullable|boolean',
            'statistics_stat_joins' => 'nullable|boolean',
            'statistics_stat_leaves' => 'nullable|boolean',
            'statistics_stat_vc' => 'nullable|boolean',
            'statistics_stat_boosts' => 'nullable|boolean',
            'statistics_update_interval_minutes' => 'nullable|integer|min:5|max:60',
        ]);

        $statsConfig = \App\Models\GuildStatisticsConfig::firstOrCreate(
            ['guild_id' => $guildModel->id],
            [
                'enabled' => false,
                'channel_name' => '📊 statistics',
                'update_interval_minutes' => 10,
                'stat_members' => true,
                'stat_joins' => true,
                'stat_leaves' => true,
                'stat_vc' => true,
                'stat_boosts' => true,
            ]
        );
        $statsConfig->update([
            'enabled' => $validated['statistics_enabled'] ?? false,
            'channel_name' => $validated['statistics_channel_name'] ?? '📊 statistics',
            'category_id' => !empty($validated['statistics_category_id']) ? $validated['statistics_category_id'] : null,
            'channel_name_members' => $validated['statistics_channel_name_members'] ?? null,
            'channel_name_joins' => $validated['statistics_channel_name_joins'] ?? null,
            'channel_name_leaves' => $validated['statistics_channel_name_leaves'] ?? null,
            'channel_name_vc' => $validated['statistics_channel_name_vc'] ?? null,
            'channel_name_boosts' => $validated['statistics_channel_name_boosts'] ?? null,
            'stat_members' => $validated['statistics_stat_members'] ?? true,
            'stat_joins' => $validated['statistics_stat_joins'] ?? true,
            'stat_leaves' => $validated['statistics_stat_leaves'] ?? true,
            'stat_vc' => $validated['statistics_stat_vc'] ?? true,
            'stat_boosts' => $validated['statistics_stat_boosts'] ?? true,
            'update_interval_minutes' => (int) ($validated['statistics_update_interval_minutes'] ?? 10),
        ]);

        return back()->with('success', 'Statistik-Channel Einstellungen gespeichert!');
    }

    public function deleteMessages(Request $request, $guild)
    {
        // Diese Methode wird für POST-Requests verwendet
        // Die GET-Route wird im DashboardController behandelt
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $validated = $request->validate([
            'channel_id' => 'required|string',
            'mode' => 'required|in:count,time,all',
            'count' => 'required_if:mode,count|integer|min:1|max:100',
            'timeHours' => 'required_if:mode,time|integer|min:0',
            'timeMinutes' => 'required_if:mode,time|integer|min:0|max:59',
            'sendNotification' => 'boolean',
            'notificationTitle' => 'nullable|string|max:256',
            'notificationDescription' => 'nullable|string|max:4096',
            'notificationColor' => 'nullable|string',
            'showFooter' => 'boolean',
        ]);

        $botToken = config('services.discord.bot_token');
        if (!$botToken) {
            return back()->with('error', 'Bot Token nicht konfiguriert!');
        }

        try {
            $channelId = $validated['channel_id'];
            $deletedCount = $this->performChannelMessageDeletion(
                $channelId,
                $botToken,
                $validated['mode'],
                $validated['count'] ?? null,
                $validated['timeHours'] ?? 0,
                $validated['timeMinutes'] ?? 0
            );

            // Sende Benachrichtigung falls aktiviert
            if ($validated['sendNotification'] ?? false && $deletedCount > 0) {
                $this->sendDeleteNotification($channelId, $deletedCount, $validated, $user);
            }
            
            $successMessage = "Erfolgreich {$deletedCount} Nachrichten gelöscht";
            return back()->with('success', $successMessage);
            
        } catch (\Exception $e) {
            \Log::error('Fehler beim Löschen der Nachrichten: ' . $e->getMessage());
            return back()->with('error', 'Fehler beim Löschen der Nachrichten: ' . $e->getMessage());
        }
    }

    /**
     * Löscht Nachrichten im Channel performant: Bulk-Delete (bis 100 pro Request) für
     * Nachrichten < 14 Tage, einzeln für ältere. Discord: Bulk-Delete max 100, nur < 14 Tage.
     */
    private function performChannelMessageDeletion(string $channelId, string $botToken, string $mode, ?int $count, int $timeHours, int $timeMinutes): int
    {
        $fourteenDaysAgoMs = (now()->timestamp - (14 * 24 * 60 * 60)) * 1000;
        $cutoffTime = $mode === 'time' ? now()->subHours($timeHours)->subMinutes($timeMinutes) : null;
        $cutoffTimestampMs = $cutoffTime ? $cutoffTime->timestamp * 1000 : null;
        $headers = ['Authorization' => 'Bot ' . $botToken];
        $deletedTotal = 0;
        $lastId = null;

        do {
            $limit = $mode === 'count' && $count !== null ? min($count - $deletedTotal, 100) : 100;
            if ($mode === 'count' && $limit <= 0) {
                break;
            }
            $url = "https://discord.com/api/v10/channels/{$channelId}/messages?limit={$limit}";
            if ($lastId) {
                $url .= "&before={$lastId}";
            }
            $response = Http::withHeaders($headers)->timeout(15)->get($url);
            if (!$response->successful() || !is_array($response->json())) {
                break;
            }
            $messages = $response->json();
            if (empty($messages)) {
                break;
            }

            // Discord liefert timestamp als ISO-8601-String; in Millisekunden für Vergleich
            $msgTimestampMs = function ($m) {
                $ts = $m['timestamp'] ?? null;
                if ($ts === null) {
                    return 0;
                }
                if (is_numeric($ts)) {
                    return (int) $ts;
                }
                $unix = strtotime($ts);
                return $unix ? $unix * 1000 : 0;
            };

            if ($mode === 'time' && $cutoffTimestampMs !== null) {
                $messages = array_values(array_filter($messages, fn ($m) => $msgTimestampMs($m) >= $cutoffTimestampMs));
                if (empty($messages)) {
                    break;
                }
            }

            $recentIds = [];
            $oldIds = [];
            foreach ($messages as $msg) {
                $tsMs = $msgTimestampMs($msg);
                $id = $msg['id'] ?? null;
                if (!$id) {
                    continue;
                }
                if ($tsMs > $fourteenDaysAgoMs) {
                    $recentIds[] = (string) $id;
                } else {
                    $oldIds[] = (string) $id;
                }
            }

            foreach (array_chunk($recentIds, 100) as $chunk) {
                if (count($chunk) >= 2) {
                    $bulk = Http::withHeaders($headers)->timeout(10)
                        ->asJson()
                        ->post("https://discord.com/api/v10/channels/{$channelId}/messages/bulk-delete", ['messages' => $chunk]);
                    if ($bulk->successful() || $bulk->status() === 204) {
                        $deletedTotal += count($chunk);
                    } else {
                        foreach ($chunk as $mid) {
                            $del = Http::withHeaders($headers)->timeout(5)->delete("https://discord.com/api/v10/channels/{$channelId}/messages/{$mid}");
                            if ($del->successful() || $del->status() === 204) {
                                $deletedTotal++;
                            }
                            usleep(250000);
                        }
                    }
                    usleep(1100000);
                } elseif (count($chunk) === 1) {
                    $del = Http::withHeaders($headers)->timeout(5)->delete("https://discord.com/api/v10/channels/{$channelId}/messages/{$chunk[0]}");
                    if ($del->successful() || $del->status() === 204) {
                        $deletedTotal++;
                    }
                    usleep(250000);
                }
            }

            foreach ($oldIds as $mid) {
                $del = Http::withHeaders($headers)->timeout(5)->delete("https://discord.com/api/v10/channels/{$channelId}/messages/{$mid}");
                if ($del->successful() || $del->status() === 204) {
                    $deletedTotal++;
                }
                usleep(250000);
            }

            $lastId = end($messages)['id'] ?? null;
            if ($mode === 'count' && $count !== null && $deletedTotal >= $count) {
                break;
            }
            if ($mode === 'time' && $cutoffTimestampMs !== null && !empty($messages) && $msgTimestampMs(end($messages)) < $cutoffTimestampMs) {
                break;
            }
            if (count($messages) < 100) {
                break;
            }
        } while (true);

        return $deletedTotal;
    }

    private function sendDeleteNotification($channelId, $count, $config, $user)
    {
        $botToken = config('services.discord.bot_token');
        if (!$botToken) {
            return;
        }

        $embed = [];
        
        if (!empty($config['notificationTitle'])) {
            $embed['title'] = str_replace(['{count}', '{channel}', '{user}'], [
                $count,
                '<#' . $channelId . '>',
                '<@' . $user->discord_id . '>'
            ], $config['notificationTitle']);
        }
        
        if (!empty($config['notificationDescription'])) {
            $embed['description'] = str_replace(['{count}', '{channel}', '{user}'], [
                $count,
                '<#' . $channelId . '>',
                '<@' . $user->discord_id . '>'
            ], $config['notificationDescription']);
        }
        
        if (!empty($config['notificationColor'])) {
            $color = str_replace('#', '', $config['notificationColor']);
            $embed['color'] = hexdec($color);
        }
        
        if ($config['showFooter'] ?? true) {
            $embed['footer'] = [
                'text' => now()->format('d.m.Y H:i'),
            ];
            $embed['timestamp'] = now()->toIso8601String();
        }

        $payload = [];
        if (!empty($embed)) {
            $payload['embeds'] = [$embed];
        }

        try {
            Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post(
                "https://discord.com/api/v10/channels/{$channelId}/messages",
                $payload
            );
        } catch (\Exception $e) {
            \Log::error('Fehler beim Senden der Benachrichtigung: ' . $e->getMessage());
        }
    }

    // Ticket System
    public function storeTicketCategory(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();

        $maxOrder = $guildModel->ticketCategories()->max('order') ?? 0;

        $guildModel->ticketCategories()->create([
            'name' => $request->name,
            'emoji' => $request->emoji,
            'description' => $request->description,
            'welcome_message' => $request->welcome_message,
            'category_id' => $request->category_id,
            'channel_name_format' => $request->channel_name_format,
            'supporter_roles' => $request->supporter_roles ?? [],
            'enabled' => $request->enabled ?? true,
            'order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Ticket-Kategorie erfolgreich erstellt!');
    }

    public function updateTicketCategory(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $category = $guildModel->ticketCategories()->findOrFail($id);

        $category->update([
            'name' => $request->name,
            'emoji' => $request->emoji,
            'description' => $request->description,
            'welcome_message' => $request->welcome_message,
            'category_id' => $request->category_id,
            'channel_name_format' => $request->channel_name_format,
            'supporter_roles' => $request->supporter_roles ?? [],
            'enabled' => $request->enabled ?? true,
        ]);

        return back()->with('success', 'Ticket-Kategorie erfolgreich aktualisiert!');
    }

    public function deleteTicketCategory($guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $category = $guildModel->ticketCategories()->findOrFail($id);
        $category->delete();

        return back()->with('success', 'Ticket-Kategorie erfolgreich gelöscht!');
    }

    public function storeTicketPost(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();

        $ticketPost = $guildModel->ticketPosts()->updateOrCreate(
            ['guild_id' => $guildModel->id],
            [
                'channel_id' => $request->channel_id,
                'embed_title' => $request->embed_title,
                'embed_description' => $request->embed_description,
                'embed_color' => $request->embed_color,
                'embed_image' => $request->embed_image,
                'embed_banner' => $request->embed_banner,
                'embed_footer' => $request->embed_footer ?? true,
                'enabled' => $request->enabled ?? true,
            ]
        );

        // Automatisch senden, wenn channel_id gesetzt ist
        if ($request->channel_id && $guildModel->ticketCategories()->where('enabled', true)->count() > 0) {
            try {
                $this->sendTicketPost($ticketPost, $guildModel);
                return back()->with('success', 'Ticket-Post erfolgreich gespeichert und gesendet!');
            } catch (\Exception $e) {
                \Log::error('Fehler beim automatischen Senden des Ticket-Posts: ' . $e->getMessage());
                return back()->with('error', 'Ticket-Post gespeichert, aber Fehler beim Senden: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Ticket-Post erfolgreich gespeichert!');
    }

    public function resendTicketPost(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $ticketPost = $guildModel->ticketPosts()->first();

        if (!$ticketPost) {
            return back()->with('error', 'Kein Ticket-Post gefunden!');
        }

        try {
            $this->sendTicketPost($ticketPost, $guildModel);
        } catch (\Exception $e) {
            \Log::error('Fehler beim erneuten Senden des Ticket-Posts: ' . $e->getMessage());
            return back()->with('error', 'Fehler beim Senden des Posts: ' . $e->getMessage());
        }

        return back()->with('success', 'Ticket-Post erfolgreich gesendet!');
    }

    private function sendTicketPost(TicketPost $ticketPost, Guild $guildModel)
    {
        $botToken = config('services.discord.bot_token');

        if (!$botToken) {
            throw new \Exception('Bot Token nicht konfiguriert.');
        }

        if (!$ticketPost->channel_id) {
            throw new \Exception('Kein Kanal ausgewählt!');
        }

        // Hole alle aktiven Kategorien
        $categories = $guildModel->ticketCategories()
            ->where('enabled', true)
            ->orderBy('order')
            ->get();

        if ($categories->isEmpty()) {
            throw new \Exception('Keine aktiven Ticket-Kategorien gefunden!');
        }

        // Baue Select Menu Options
        $selectMenuOptions = [];
        foreach ($categories as $category) {
            $label = ($category->emoji ? $category->emoji . ' ' : '') . $category->name;
            // Discord limit: label max 100 chars, description max 100 chars, value max 100 chars
            $selectMenuOptions[] = [
                'label' => mb_substr($label, 0, 100),
                'value' => 'ticket_category_' . $category->id,
                'description' => $category->description ? mb_substr($category->description, 0, 100) : null,
            ];
        }

        // Discord limit: max 25 options in select menu
        if (count($selectMenuOptions) > 25) {
            $selectMenuOptions = array_slice($selectMenuOptions, 0, 25);
        }

        // Baue Embed
        $embed = [];
        if ($ticketPost->embed_title) {
            $embed['title'] = $ticketPost->embed_title;
        }
        if ($ticketPost->embed_description) {
            $embed['description'] = $ticketPost->embed_description;
        }
        if ($ticketPost->embed_color) {
            $color = str_replace('#', '', $ticketPost->embed_color);
            $embed['color'] = hexdec($color);
        }
        if ($ticketPost->embed_banner) {
            $embed['image'] = ['url' => $ticketPost->embed_banner];
        } elseif ($ticketPost->embed_image) {
            $embed['image'] = ['url' => $ticketPost->embed_image];
        }
        if ($ticketPost->embed_footer) {
            $embed['footer'] = [
                'text' => $guildModel->name . ' • ' . now()->format('d.m.Y H:i'),
            ];
        }

        // Hole Server-Sprache für Placeholder
        $language = $guildModel->language ?? 'de';
        $placeholders = [
            'de' => 'Wähle eine Kategorie...',
            'en' => 'Select a category...',
            'tr' => 'Bir kategori seçin...',
        ];
        $placeholder = $placeholders[$language] ?? $placeholders['de'];

        // Baue Message mit Select Menu
        $components = [[
            'type' => 1, // Action Row
            'components' => [[
                'type' => 3, // Select Menu
                'custom_id' => 'ticket_category_select',
                'placeholder' => $placeholder,
                'options' => $selectMenuOptions,
            ]],
        ]];

        $payload = [
            'embeds' => !empty($embed) ? [$embed] : [],
            'components' => $components,
        ];

        \Log::info('Sende Ticket-Post:', [
            'channel_id' => $ticketPost->channel_id,
            'categories_count' => count($selectMenuOptions),
            'payload' => json_encode($payload, JSON_PRETTY_PRINT),
        ]);

        // Sende Message
        $response = Http::withHeaders([
            'Authorization' => 'Bot ' . $botToken,
            'Content-Type' => 'application/json',
        ])->timeout(10)->post(
            "https://discord.com/api/v10/channels/{$ticketPost->channel_id}/messages",
            $payload
        );

        if (!$response->successful()) {
            $errorBody = $response->json();
            $errorMessage = $errorBody['message'] ?? 'Unbekannter Fehler';
            \Log::error('Discord API Error:', [
                'status' => $response->status(),
                'error' => $errorMessage,
            ]);
            throw new \Exception('Fehler beim Senden der Nachricht: ' . $errorMessage . ' (Status: ' . $response->status() . ')');
        }

        $messageData = $response->json();
        $messageId = $messageData['id'];

        // Update message_id in database
        $ticketPost->update(['message_id' => $messageId]);
    }

    public function getTicketTranscript($guild, $ticketId)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return response()->json(['error' => 'Kein Zugriff auf diesen Server.'], 403);
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $ticket = Ticket::where('id', $ticketId)
            ->where('guild_id', $guildModel->id)
            ->where('status', 'closed')
            ->whereNotNull('transcript')
            ->firstOrFail();

        return response($ticket->transcript, 200)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="ticket-transcript-' . $ticketId . '.html"');
    }

    public function deleteTicketTranscript($guild, $ticketId)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $ticket = Ticket::where('id', $ticketId)
            ->where('guild_id', $guildModel->id)
            ->where('status', 'closed')
            ->whereNotNull('transcript')
            ->firstOrFail();

        $ticket->update(['transcript' => null]);

        return redirect()->route('guild.ticket-system', ['guild' => $guild])
            ->with('success', 'Transcript erfolgreich gelöscht.');
    }

    public function deleteMultipleTicketTranscripts(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $validated = $request->validate([
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'required|integer',
        ]);

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
        $deleted = Ticket::where('guild_id', $guildModel->id)
            ->where('status', 'closed')
            ->whereNotNull('transcript')
            ->whereIn('id', $validated['ticket_ids'])
            ->update(['transcript' => null]);

        return redirect()->route('guild.ticket-system', ['guild' => $guild])
            ->with('success', $deleted . ' Transcript(s) erfolgreich gelöscht.');
    }

    public function updateTicketTranscriptSetting(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
        $guildModel->update([
            'ticket_transcript_enabled' => $request->transcript_enabled ?? true,
        ]);

        return back()->with('success', 'Transcript-Einstellung erfolgreich gespeichert!');
    }

    public function updateTicketCloseConfig(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
        $validated = $request->validate([
            'require_confirmation' => 'boolean',
            'close_message' => 'nullable|string|max:2000',
            'confirmation_button_text' => 'nullable|string|max:80',
        ]);
        
        $guildModel->update([
            'ticket_close_require_confirmation' => $validated['require_confirmation'] ?? false,
            'ticket_close_message' => $validated['close_message'] ?? null,
            'ticket_close_confirmation_button_text' => $validated['confirmation_button_text'] ?? null,
        ]);

        return back()->with('success', 'Ticket-Close-Einstellungen erfolgreich gespeichert!');
    }

    public function storeAutoDeleteMessage(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
        $validated = $request->validate([
            'channel_id' => 'required|string',
            'interval_minutes' => 'required|in:5,10,30,60',
            'enabled' => 'boolean',
        ]);
        
        // Setze delete_count auf 100 (wird nicht mehr verwendet, aber für Kompatibilität)
        $validated['delete_count'] = 100;
        
        // Prüfe ob bereits eine Auto-Delete für diesen Channel existiert
        $existing = $guildModel->autoDeleteMessages()
            ->where('channel_id', $validated['channel_id'])
            ->first();
            
        if ($existing) {
            return back()->with('error', 'Für diesen Channel existiert bereits eine automatische Löschung.');
        }
        
        $guildModel->autoDeleteMessages()->create($validated);

        return back()->with('success', 'Automatische Löschung erfolgreich erstellt!');
    }

    public function updateAutoDeleteMessage(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $autoDelete = $guildModel->autoDeleteMessages()->findOrFail($id);
        
        $validated = $request->validate([
            'channel_id' => 'required|string',
            'interval_minutes' => 'required|in:5,10,30,60',
            'enabled' => 'boolean',
        ]);
        
        // Setze delete_count auf 100 (wird nicht mehr verwendet, aber für Kompatibilität)
        $validated['delete_count'] = 100;
        
        // Prüfe ob bereits eine Auto-Delete für diesen Channel existiert (außer der aktuellen)
        $existing = $guildModel->autoDeleteMessages()
            ->where('channel_id', $validated['channel_id'])
            ->where('id', '!=', $id)
            ->first();
            
        if ($existing) {
            return back()->with('error', 'Für diesen Channel existiert bereits eine automatische Löschung.');
        }
        
        $autoDelete->update($validated);

        return back()->with('success', 'Automatische Löschung erfolgreich aktualisiert!');
    }

    public function toggleAutoDeleteMessage(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $autoDelete = $guildModel->autoDeleteMessages()->findOrFail($id);
        
        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);
        
        $autoDelete->update(['enabled' => $validated['enabled']]);

        return back()->with('success', 'Automatische Löschung erfolgreich ' . ($validated['enabled'] ? 'aktiviert' : 'deaktiviert') . '!');
    }

    public function deleteAutoDeleteMessage($guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $autoDelete = $guildModel->autoDeleteMessages()->findOrFail($id);
        
        $autoDelete->delete();

        return back()->with('success', 'Automatische Löschung erfolgreich gelöscht!');
    }

    public function storeGiveaway(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
        // Get server language
        $language = $guildModel->language ?? 'de';

        $validated = $request->validate([
            'title' => 'required|string|max:256',
            'description' => 'nullable|string|max:2000',
            'channel_id' => 'required|string',
            'prize_type' => 'required|in:code,role,custom',
            'prize_codes' => 'nullable|array|required_if:prize_type,code',
            'prize_codes.*' => 'required|string|max:255',
            'prize_role_id' => 'nullable|string|required_if:prize_type,role',
            'prize_custom' => 'nullable|string|max:500|required_if:prize_type,custom',
            'winner_count' => 'required|integer|min:1|max:100',
            'duration_weeks' => 'nullable|integer|min:0',
            'duration_days' => 'nullable|integer|min:0|max:6',
            'duration_hours' => 'nullable|integer|min:0|max:23',
            'duration_minutes' => 'nullable|integer|min:0|max:59',
            'winner_message' => 'nullable|string|max:2000',
            'ticket_message' => 'nullable|string|max:2000',
        ]);

        // Validate that prize_codes count matches winner_count for code type
        if ($validated['prize_type'] === 'code') {
            $codeCount = count($validated['prize_codes'] ?? []);
            if ($codeCount !== $validated['winner_count']) {
                return back()->withErrors(['prize_codes' => "Die Anzahl der Codes muss der Anzahl der Gewinner ({$validated['winner_count']}) entsprechen."])->withInput();
            }
        }

        // Validate that at least one duration field is set
        $hasDuration = ($validated['duration_weeks'] ?? 0) > 0 
                    || ($validated['duration_days'] ?? 0) > 0 
                    || ($validated['duration_hours'] ?? 0) > 0 
                    || ($validated['duration_minutes'] ?? 0) > 0;
        
        if (!$hasDuration) {
            return back()->withErrors(['duration' => 'Mindestens eine Dauer muss angegeben werden.'])->withInput();
        }

        // Calculate ends_at
        $endsAt = now();
        if ($validated['duration_weeks'] ?? 0) {
            $endsAt->addWeeks($validated['duration_weeks']);
        }
        if ($validated['duration_days'] ?? 0) {
            $endsAt->addDays($validated['duration_days']);
        }
        if ($validated['duration_hours'] ?? 0) {
            $endsAt->addHours($validated['duration_hours']);
        }
        if ($validated['duration_minutes'] ?? 0) {
            $endsAt->addMinutes($validated['duration_minutes']);
        }

        // Send giveaway to Discord
        $botToken = config('services.discord.bot_token');
        if (!$botToken) {
            return back()->with('error', 'Bot Token nicht konfiguriert.');
        }

        try {
            // Get translations based on server language
            $translations = $this->getGiveawayTranslations($language);
            
            // Create embed and button via Discord API
            $embed = [
                'title' => '🎉 ' . $validated['title'],
                'description' => $validated['description'] ?? $translations['giveawayDescription'],
                'color' => 0x5865f2,
                'timestamp' => $endsAt->toIso8601String(),
                'fields' => [
                    [
                        'name' => $translations['endsAt'],
                        'value' => '<t:' . $endsAt->timestamp . ':R>',
                        'inline' => true,
                    ],
                    [
                        'name' => $translations['participants'],
                        'value' => '0',
                        'inline' => true,
                    ],
                    [
                        'name' => $translations['winners'],
                        'value' => (string)$validated['winner_count'],
                        'inline' => true,
                    ],
                ],
            ];

            if ($validated['prize_type'] === 'code' && !empty($validated['prize_codes'])) {
                $embed['fields'][] = [
                    'name' => $translations['prize'],
                    'value' => '🎁 ' . $translations['code'],
                    'inline' => false,
                ];
            } elseif ($validated['prize_type'] === 'role' && $validated['prize_role_id']) {
                $embed['fields'][] = [
                    'name' => $translations['prize'],
                    'value' => '🎭 ' . $translations['discordRole'],
                    'inline' => false,
                ];
            } elseif ($validated['prize_custom']) {
                $embed['fields'][] = [
                    'name' => $translations['prize'],
                    'value' => $validated['prize_custom'],
                    'inline' => false,
                ];
            }

            $components = [
                [
                    'type' => 1, // Action Row
                    'components' => [
                        [
                            'type' => 2, // Button
                            'style' => 1, // Primary
                            'label' => $translations['participate'],
                            'custom_id' => 'giveaway_join_temp', // Will be updated after creation
                        ],
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
                'Content-Type' => 'application/json',
            ])->post("https://discord.com/api/v10/channels/{$validated['channel_id']}/messages", [
                'embeds' => [$embed],
                'components' => $components,
            ]);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? 'Unbekannter Fehler';
                \Log::error('Discord API Error beim Senden des Giveaways:', [
                    'status' => $response->status(),
                    'error' => $errorMessage,
                ]);
                return back()->with('error', 'Fehler beim Senden des Giveaways: ' . $errorMessage);
            }

            $messageData = $response->json();
            $messageId = $messageData['id'];

            // Create giveaway in database
            $giveaway = $guildModel->giveaways()->create([
                'channel_id' => $validated['channel_id'],
                'message_id' => $messageId,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'prize_type' => $validated['prize_type'],
                'prize_code' => ($validated['prize_type'] === 'code' && !empty($validated['prize_codes'])) ? json_encode($validated['prize_codes']) : null,
                'prize_role_id' => $validated['prize_role_id'] ?? null,
                'prize_custom' => $validated['prize_custom'] ?? null,
                'winner_count' => $validated['winner_count'],
                'ends_at' => $endsAt,
                'winner_message' => $validated['winner_message'] ?? null,
                'ticket_message' => $validated['ticket_message'] ?? null,
            ]);

            // Update button with correct giveaway ID
            $components[0]['components'][0]['custom_id'] = 'giveaway_join_' . $giveaway->id;
            
            Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
                'Content-Type' => 'application/json',
            ])->patch("https://discord.com/api/v10/channels/{$validated['channel_id']}/messages/{$messageId}", [
                'embeds' => [$embed],
                'components' => $components,
            ]);

            return back()->with('success', 'Giveaway erfolgreich erstellt!');
        } catch (\Exception $e) {
            \Log::error('Giveaway Erstellung Fehler', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'guild' => $guild,
                'request' => $request->all(),
            ]);
            return back()->with('error', 'Fehler beim Erstellen des Giveaways: ' . $e->getMessage())->withInput();
        }
    }

    public function deleteGiveaway(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $giveaway = $guildModel->giveaways()->findOrFail($id);

        // Delete message from Discord if exists
        if ($giveaway->message_id && $giveaway->channel_id) {
            $botToken = config('services.discord.bot_token');
            if ($botToken) {
                try {
                    Http::withHeaders([
                        'Authorization' => 'Bot ' . $botToken,
                    ])->delete("https://discord.com/api/v10/channels/{$giveaway->channel_id}/messages/{$giveaway->message_id}");
                } catch (\Exception $e) {
                    // Ignore errors
                }
            }
        }

        $giveaway->delete();

        return back()->with('success', 'Giveaway erfolgreich gelöscht!');
    }

    public function storeBirthday(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();

        $validated = $request->validate([
            'user_id' => 'required|string',
            'birthday' => 'required|date',
            // Geburtstags-Konfiguration (optional, wird mitgespeichert)
            'enabled' => 'nullable|boolean',
            'channel_id' => 'nullable|string',
            'birthday_role_id' => 'nullable|string',
            'role_id' => 'nullable|string',
            'embed_title' => 'nullable|string|max:256',
            'embed_description' => 'nullable|string|max:2000',
            'embed_color' => 'nullable|string|max:7',
            'embed_thumbnail' => 'nullable|string|max:500',
            'embed_image' => 'nullable|string|max:500',
        ]);

        $guildModel->birthdays()->updateOrCreate(
            ['user_id' => $validated['user_id']],
            ['birthday' => $validated['birthday']]
        );

        // Geburtstags-Konfiguration speichern (Rolle, Channel, Enabled, Embed)
        $configData = array_filter([
            'enabled' => $request->boolean('enabled'),
            'channel_id' => $validated['channel_id'] ?? null,
            'role_id' => $validated['birthday_role_id'] ?? $validated['role_id'] ?? null,
            'embed_title' => $validated['embed_title'] ?? null,
            'embed_description' => $validated['embed_description'] ?? null,
            'embed_color' => $validated['embed_color'] ?? null,
            'embed_thumbnail' => $validated['embed_thumbnail'] ?? null,
            'embed_image' => $validated['embed_image'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        if (!empty($configData)) {
            $guildModel->birthdayConfig()->updateOrCreate(
                ['guild_id' => $guildModel->id],
                $configData
            );
        }

        return back()->with('success', 'Geburtstag erfolgreich hinzugefügt!');
    }

    public function updateBirthday(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $birthday = $guildModel->birthdays()->findOrFail($id);

        $validated = $request->validate([
            'birthday' => 'required|date',
        ]);

        $birthday->update(['birthday' => $validated['birthday']]);

        return back()->with('success', 'Geburtstag erfolgreich aktualisiert!');
    }

    public function deleteBirthday(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $birthday = $guildModel->birthdays()->findOrFail($id);
        $birthday->delete();

        return back()->with('success', 'Geburtstag erfolgreich gelöscht!');
    }

    public function updateBirthdayConfig(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'channel_id' => 'nullable|string',
            'role_id' => 'nullable|string',
            'embed_title' => 'nullable|string|max:256',
            'embed_description' => 'nullable|string|max:2000',
            'embed_color' => 'nullable|string|max:7',
            'embed_thumbnail' => 'nullable|string|max:500',
            'embed_image' => 'nullable|string|max:500',
        ]);

        $guildModel->birthdayConfig()->updateOrCreate(
            ['guild_id' => $guildModel->id],
            $validated
        );

        return back()->with('success', 'Geburtstags-Konfiguration erfolgreich gespeichert!');
    }

    /**
     * Prüft ob der Benutzer die Berechtigung hat, den Server zu verwalten
     */

    /**
     * Gibt die Übersetzungen für Giveaways basierend auf der Sprache zurück
     */
    private function getGiveawayTranslations($language = 'de')
    {
        $translations = [
            'de' => [
                'giveawayDescription' => 'Klicke auf den Button unten, um am Gewinnspiel teilzunehmen!',
                'endsAt' => 'Endet',
                'participants' => 'Teilnehmer',
                'winners' => 'Gewinner',
                'participate' => 'Teilnehmen',
                'prize' => 'Preis',
                'code' => 'Code',
                'discordRole' => 'Discord Rolle',
            ],
            'en' => [
                'giveawayDescription' => 'Click the button below to participate in the giveaway!',
                'endsAt' => 'Ends',
                'participants' => 'Participants',
                'winners' => 'Winners',
                'participate' => 'Participate',
                'prize' => 'Prize',
                'code' => 'Code',
                'discordRole' => 'Discord Role',
            ],
            'tr' => [
                'giveawayDescription' => 'Çekilişe katılmak için aşağıdaki butona tıklayın!',
                'endsAt' => 'Biter',
                'participants' => 'Katılımcılar',
                'winners' => 'Kazananlar',
                'participate' => 'Katıl',
                'prize' => 'Ödül',
                'code' => 'Kod',
                'discordRole' => 'Discord Rolü',
            ],
        ];

        return $translations[$language] ?? $translations['de'];
    }

    /**
     * Toggle Add-On Status
     */
    public function toggleAddOn(Request $request, $guild)
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

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();

        $validated = $request->validate([
            'addon_type' => 'required|string',
            'enabled' => 'required|boolean',
        ]);

        // Validiere Add-On-Typ
        $allowedTypes = ['team_management', 'faction_management', 'embed_sender'];
        if (!in_array($validated['addon_type'], $allowedTypes)) {
            return redirect()->route('guild.config', ['guild' => $guild])
                ->with('error', 'Ungültiger Add-On-Typ.');
        }

        AddOn::updateOrCreate(
            [
                'guild_id' => $guildModel->id,
                'addon_type' => $validated['addon_type'],
            ],
            [
                'enabled' => $validated['enabled'],
            ]
        );

        return redirect()->route('guild.config', ['guild' => $guild])
            ->with('success', 'Add-On erfolgreich ' . ($validated['enabled'] ? 'aktiviert' : 'deaktiviert') . '!');
    }

    /**
     * Store Team Rank
     */
    public function storeTeamRank(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role_id' => 'required|string',
        ]);

        // Prüfe ob dieser Rang (role_id) bereits für diesen Server existiert
        $existingRank = TeamRank::where('guild_id', $guildModel->id)
            ->where('role_id', $validated['role_id'])
            ->first();

        if ($existingRank) {
            return back()->with('error', 'Dieser Rang existiert bereits für diesen Server. Jede Discord-Rolle kann nur einmal als Team-Rang verwendet werden.');
        }

        $maxSortOrder = TeamRank::where('guild_id', $guildModel->id)->max('sort_order') ?? -1;

        TeamRank::create([
            'guild_id' => $guildModel->id,
            'name' => $validated['name'],
            'role_id' => $validated['role_id'],
            'sort_order' => $maxSortOrder + 1,
            'visible' => true,
        ]);

        return back()->with('success', 'Rang erfolgreich hinzugefügt!');
    }

    /**
     * Update Team Rank
     */
    public function updateTeamRank(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $rank = TeamRank::where('guild_id', $guildModel->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role_id' => 'required|string',
        ]);

        // Prüfe ob diese Rolle bereits von einem anderen Rang verwendet wird
        $existingRank = TeamRank::where('guild_id', $guildModel->id)
            ->where('role_id', $validated['role_id'])
            ->where('id', '!=', $id) // Ignoriere den aktuellen Rang
            ->first();

        if ($existingRank) {
            return back()->with('error', 'Diese Discord-Rolle wird bereits von einem anderen Rang verwendet. Jede Discord-Rolle kann nur einmal als Team-Rang verwendet werden.');
        }

        $rank->update([
            'name' => $validated['name'],
            'role_id' => $validated['role_id'],
        ]);

        return back()->with('success', 'Rang erfolgreich aktualisiert!');
    }

    /**
     * Delete Team Rank
     */
    public function deleteTeamRank(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $rank = TeamRank::where('guild_id', $guildModel->id)->findOrFail($id);
        $rank->delete();

        return back()->with('success', 'Rang erfolgreich gelöscht!');
    }

    /**
     * Toggle Team Rank Visibility
     */
    public function toggleTeamRankVisibility(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $rank = TeamRank::where('guild_id', $guildModel->id)->findOrFail($id);

        $validated = $request->validate([
            'visible' => 'required|boolean',
        ]);

        $rank->update(['visible' => $validated['visible']]);

        return back();
    }

    /**
     * Move Team Rank
     */
    public function moveTeamRank(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $rank = TeamRank::where('guild_id', $guildModel->id)->findOrFail($id);

        $validated = $request->validate([
            'direction' => 'required|in:up,down',
        ]);

        $allRanks = TeamRank::where('guild_id', $guildModel->id)
            ->orderBy('sort_order')
            ->get();

        $currentIndex = $allRanks->search(function ($r) use ($id) {
            return $r->id == $id;
        });

        if ($validated['direction'] === 'up' && $currentIndex > 0) {
            $swapRank = $allRanks[$currentIndex - 1];
            $tempOrder = $rank->sort_order;
            $rank->update(['sort_order' => $swapRank->sort_order]);
            $swapRank->update(['sort_order' => $tempOrder]);
        } elseif ($validated['direction'] === 'down' && $currentIndex < $allRanks->count() - 1) {
            $swapRank = $allRanks[$currentIndex + 1];
            $tempOrder = $rank->sort_order;
            $rank->update(['sort_order' => $swapRank->sort_order]);
            $swapRank->update(['sort_order' => $tempOrder]);
        }

        return back();
    }

    /**
     * Store Team Member
     */
    public function storeTeamMember(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();

        $validated = $request->validate([
            'user_id' => 'required|string',
            'rank_id' => 'required|exists:team_ranks,id',
        ]);

        // Lade den Rang, um die role_id zu bekommen
        $rank = TeamRank::where('guild_id', $guildModel->id)
            ->findOrFail($validated['rank_id']);

        if (!$rank->role_id) {
            return back()->with('error', 'Dieser Rang hat keine Discord-Rolle zugewiesen.');
        }

        // Speichere Mitglied in Datenbank
        $teamMember = TeamMember::updateOrCreate(
            [
                'guild_id' => $guildModel->id,
                'user_id' => $validated['user_id'],
            ],
            [
                'rank_id' => $validated['rank_id'],
            ]
        );

        // Weise die Discord-Rolle zu
        $botToken = config('services.discord.bot_token');
        if (!$botToken) {
            return back()->with('error', 'Bot Token nicht konfiguriert!');
        }

        try {
            // Hole aktuelle Rollen des Mitglieds
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->timeout(10)->get("https://discord.com/api/v10/guilds/{$guild}/members/{$validated['user_id']}");

            if (!$response->successful()) {
                \Log::error('Fehler beim Abrufen der Mitglieder-Daten:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return back()->with('error', 'Fehler beim Abrufen der Mitglieder-Daten. Stelle sicher, dass der Benutzer auf dem Server ist.');
            }

            $memberData = $response->json();
            $currentRoles = $memberData['roles'] ?? [];

            // Füge die neue Rolle hinzu, falls noch nicht vorhanden
            if (!in_array($rank->role_id, $currentRoles)) {
                $currentRoles[] = $rank->role_id;

                // Aktualisiere Rollen
                $updateResponse = Http::withHeaders([
                    'Authorization' => 'Bot ' . $botToken,
                ])->timeout(10)->patch("https://discord.com/api/v10/guilds/{$guild}/members/{$validated['user_id']}", [
                    'roles' => array_values($currentRoles),
                ]);

                if (!$updateResponse->successful()) {
                    \Log::error('Fehler beim Zuweisen der Rolle:', [
                        'status' => $updateResponse->status(),
                        'body' => $updateResponse->body()
                    ]);
                    return back()->with('error', 'Mitglied wurde hinzugefügt, aber die Rolle konnte nicht zugewiesen werden. Bitte manuell zuweisen.');
                }
            }

            // Aktualisiere Team-Liste
            $this->updateTeamList($guild, $guildModel);

            return back()->with('success', 'Mitglied erfolgreich hinzugefügt und Rolle zugewiesen!');
        } catch (\Exception $e) {
            \Log::error('Exception beim Zuweisen der Rolle:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Mitglied wurde hinzugefügt, aber die Rolle konnte nicht zugewiesen werden: ' . $e->getMessage());
        }
    }

    /**
     * Delete Team Member
     */
    public function deleteTeamMember(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $member = TeamMember::where('guild_id', $guildModel->id)->findOrFail($id);
        
        $teamConfig = $guildModel->teamManagementConfig;
        
        if (!$teamConfig || !$teamConfig->default_role_id) {
            return redirect()->route('guild.team-management', ['guild' => $guild])
                ->with('error', 'Keine Default-Rolle konfiguriert. Bitte zuerst eine Default-Rolle in den Einstellungen festlegen.');
        }

        $botToken = config('services.discord.bot_token');
        if (!$botToken) {
            return redirect()->route('guild.team-management', ['guild' => $guild])
                ->with('error', 'Bot Token nicht konfiguriert!');
        }

        try {
            // Hole aktuelle Rollen des Mitglieds
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->timeout(10)->get("https://discord.com/api/v10/guilds/{$guild}/members/{$member->user_id}");

            if ($response->successful()) {
                $memberData = $response->json();
                $currentRoles = $memberData['roles'] ?? [];

                // Entferne ALLE Rollen und weise nur Default-Rolle zu
                $newRoles = [$teamConfig->default_role_id];

                // Aktualisiere Rollen
                $updateResponse = Http::withHeaders([
                    'Authorization' => 'Bot ' . $botToken,
                ])->timeout(10)->patch("https://discord.com/api/v10/guilds/{$guild}/members/{$member->user_id}", [
                    'roles' => array_values($newRoles),
                ]);

                if (!$updateResponse->successful()) {
                    \Log::error('Fehler beim Entfernen aller Rollen:', [
                        'status' => $updateResponse->status(),
                        'body' => $updateResponse->body()
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Fehler beim Entfernen der Rollen:', [
                'error' => $e->getMessage()
            ]);
            // Fortfahren mit dem Löschen, auch wenn Rollen-Entfernung fehlschlägt
        }
        
        $member->delete();

        // Aktualisiere Team-Liste
        $this->updateTeamList($guild, $guildModel);

        return back()->with('success', 'Mitglied erfolgreich entfernt und alle Rechte entzogen!');
    }

    /**
     * Update Team Management Config
     */
    public function updateTeamManagementConfig(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();

        $validated = $request->validate([
            'channel_id' => 'nullable|string',
            'team_list_channel_id' => 'nullable|string',
            'default_role_id' => 'nullable|string',
            'notify_join' => 'boolean',
            'notify_leave' => 'boolean',
            'notify_upgrade' => 'boolean',
            'notify_downgrade' => 'boolean',
        ]);

        $guildModel->teamManagementConfig()->updateOrCreate(
            ['guild_id' => $guildModel->id],
            $validated
        );

        // Wenn team_list_channel_id gesetzt wurde, erstelle/aktualisiere die Team-Liste
        if (!empty($validated['team_list_channel_id'])) {
            $this->updateTeamList($guild, $guildModel);
        }

        return back()->with('success', 'Konfiguration erfolgreich gespeichert!');
    }

    /**
     * Update Faction Management Config
     */
    public function updateFactionManagementConfig(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();

        $validated = $request->validate([
            'channel_id_create' => 'nullable|string|max:32',
            'channel_id_warn' => 'nullable|string|max:32',
            'channel_id_dissolve' => 'nullable|string|max:32',
            'channel_id_overview' => 'nullable|string|max:32',
            'embed_create' => 'nullable|array',
            'embed_warn' => 'nullable|array',
            'embed_dissolve' => 'nullable|array',
            'embed_overview' => 'nullable|array',
        ]);

        $data = [
            'channel_id_create' => $validated['channel_id_create'] ?? null,
            'channel_id_warn' => $validated['channel_id_warn'] ?? null,
            'channel_id_dissolve' => $validated['channel_id_dissolve'] ?? null,
            'channel_id_overview' => $validated['channel_id_overview'] ?? null,
        ];
        $normalizeFactionEmbedDescription = function (array $embed) {
            if (isset($embed['description']) && is_string($embed['description'])) {
                $embed['description'] = str_replace(["\n\n", '\\n\\n', '\n\n'], ' ', $embed['description']);
            }
            return $embed;
        };
        if (isset($validated['embed_create'])) {
            $data['embed_create'] = $normalizeFactionEmbedDescription($validated['embed_create']);
        }
        if (isset($validated['embed_warn'])) {
            $data['embed_warn'] = $normalizeFactionEmbedDescription($validated['embed_warn']);
        }
        if (isset($validated['embed_dissolve'])) {
            $data['embed_dissolve'] = $normalizeFactionEmbedDescription($validated['embed_dissolve']);
        }
        if (isset($validated['embed_overview'])) {
            $data['embed_overview'] = $validated['embed_overview'];
        }

        $guildModel->factionManagementConfig()->updateOrCreate(
            ['guild_id' => $guildModel->id],
            $data
        );

        return back()->with('success', 'Fraktion-Konfiguration gespeichert!');
    }

    /**
     * Remove team rights from member (assign default role)
     */
    public function removeTeamRights(Request $request, $guild, $id)
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

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $member = TeamMember::where('guild_id', $guildModel->id)->findOrFail($id);
        
        $validated = $request->validate([
            'rank_ids' => 'required|array',
            'rank_ids.*' => 'exists:team_ranks,id',
        ]);

        $teamConfig = $guildModel->teamManagementConfig;
        
        if (!$teamConfig || !$teamConfig->default_role_id) {
            return redirect()->route('guild.team-management', ['guild' => $guild])
                ->with('error', 'Keine Default-Rolle konfiguriert. Bitte zuerst eine Default-Rolle in den Einstellungen festlegen.');
        }

        // Entferne ausgewählte Team-Rollen und weise Default-Rolle zu
        $botToken = config('services.discord.bot_token');
        if (!$botToken) {
            return redirect()->route('guild.team-management', ['guild' => $guild])
                ->with('error', 'Bot Token nicht konfiguriert!');
        }

        try {
            // Hole die ausgewählten Team-Ränge
            $selectedRanks = TeamRank::where('guild_id', $guildModel->id)
                ->whereIn('id', $validated['rank_ids'])
                ->whereNotNull('role_id')
                ->get();

            $roleIdsToRemove = $selectedRanks->pluck('role_id')->toArray();
            $defaultRoleId = $teamConfig->default_role_id;

            // Hole aktuelle Rollen des Mitglieds
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->get("https://discord.com/api/v10/guilds/{$guild}/members/{$member->user_id}");

            if (!$response->successful()) {
                \Log::error('Fehler beim Abrufen der Mitglieder-Daten:', $response->json());
                return redirect()->route('guild.team-management', ['guild' => $guild])
                    ->with('error', 'Fehler beim Abrufen der Mitglieder-Daten.');
            }

            $memberData = $response->json();
            $currentRoles = $memberData['roles'] ?? [];

            // Entferne ausgewählte Team-Rollen und füge Default-Rolle hinzu
            $newRoles = array_filter($currentRoles, function($roleId) use ($roleIdsToRemove) {
                return !in_array($roleId, $roleIdsToRemove);
            });

            if (!in_array($defaultRoleId, $newRoles)) {
                $newRoles[] = $defaultRoleId;
            }

            // Aktualisiere Rollen
            $updateResponse = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->patch("https://discord.com/api/v10/guilds/{$guild}/members/{$member->user_id}", [
                'roles' => array_values($newRoles),
            ]);

            if (!$updateResponse->successful()) {
                \Log::error('Fehler beim Entfernen der Team-Rechte:', $updateResponse->json());
                return redirect()->route('guild.team-management', ['guild' => $guild])
                    ->with('error', 'Fehler beim Entfernen der Team-Rechte.');
            }

            // Aktualisiere Team-Liste
            $this->updateTeamList($guild, $guildModel);

            return redirect()->route('guild.team-management', ['guild' => $guild])
                ->with('success', 'Team-Rechte erfolgreich entfernt und Default-Rolle zugewiesen.');
        } catch (\Exception $e) {
            \Log::error('Fehler beim Entfernen der Team-Rechte:', ['error' => $e->getMessage()]);
            return redirect()->route('guild.team-management', ['guild' => $guild])
                ->with('error', 'Fehler beim Entfernen der Team-Rechte: ' . $e->getMessage());
        }
    }

    /**
     * Add additional role to team member
     */
    public function addTeamMemberRole(Request $request, $guild, $id)
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

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $member = TeamMember::where('guild_id', $guildModel->id)->findOrFail($id);
        
        $validated = $request->validate([
            'role_id' => 'required|string',
            'send_announcement' => 'boolean',
            'template_id' => 'nullable|exists:team_announcement_templates,id',
        ]);

        $botToken = config('services.discord.bot_token');
        if (!$botToken) {
            return redirect()->route('guild.team-management', ['guild' => $guild])
                ->with('error', 'Bot Token nicht konfiguriert!');
        }

        try {
            // Hole aktuelle Rollen des Mitglieds
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->timeout(10)->get("https://discord.com/api/v10/guilds/{$guild}/members/{$member->user_id}");

            if (!$response->successful()) {
                return redirect()->route('guild.team-management', ['guild' => $guild])
                    ->with('error', 'Fehler beim Abrufen der Mitglieder-Daten.');
            }

            $memberData = $response->json();
            $currentRoles = $memberData['roles'] ?? [];

            // Füge die neue Rolle hinzu, falls noch nicht vorhanden
            if (!in_array($validated['role_id'], $currentRoles)) {
                $currentRoles[] = $validated['role_id'];

                // Aktualisiere Rollen
                $updateResponse = Http::withHeaders([
                    'Authorization' => 'Bot ' . $botToken,
                ])->timeout(10)->patch("https://discord.com/api/v10/guilds/{$guild}/members/{$member->user_id}", [
                    'roles' => array_values($currentRoles),
                ]);

                if (!$updateResponse->successful()) {
                    return redirect()->route('guild.team-management', ['guild' => $guild])
                        ->with('error', 'Rolle konnte nicht zugewiesen werden.');
                }
            }

            // Sende Ankündigung, falls gewünscht
            if (($validated['send_announcement'] ?? false) && !empty($validated['template_id'])) {
                $template = TeamAnnouncementTemplate::where('guild_id', $guildModel->id)
                    ->findOrFail($validated['template_id']);

                $teamConfig = $guildModel->teamManagementConfig;
                if ($teamConfig && $teamConfig->channel_id) {
                    \Log::info('Sende Team-Ankündigung:', [
                        'guild' => $guild,
                        'channel_id' => $teamConfig->channel_id,
                        'template_id' => $template->id,
                        'user_id' => $member->user_id,
                        'role_id' => $validated['role_id']
                    ]);
                    $this->sendTeamAnnouncement($guild, $teamConfig->channel_id, $template, $member->user_id, $validated['role_id']);
                } else {
                    \Log::warning('Ankündigung nicht gesendet: Kein Channel konfiguriert', [
                        'guild' => $guild,
                        'has_config' => $teamConfig ? true : false,
                        'channel_id' => $teamConfig->channel_id ?? null
                    ]);
                }
            } else {
                \Log::info('Ankündigung nicht gesendet:', [
                    'send_announcement' => $validated['send_announcement'] ?? false,
                    'template_id' => $validated['template_id'] ?? null
                ]);
            }

            // Aktualisiere Team-Liste
            $this->updateTeamList($guild, $guildModel);

            return redirect()->route('guild.team-management', ['guild' => $guild])
                ->with('success', 'Rolle erfolgreich hinzugefügt!');
        } catch (\Exception $e) {
            \Log::error('Fehler beim Hinzufügen der Rolle:', ['error' => $e->getMessage()]);
            return redirect()->route('guild.team-management', ['guild' => $guild])
                ->with('error', 'Fehler beim Hinzufügen der Rolle: ' . $e->getMessage());
        }
    }

    /**
     * Store Team Announcement Template
     */
    public function storeTeamAnnouncementTemplate(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:join,leave,upgrade,downgrade,custom',
            'embed' => 'nullable|array',
            'enabled' => 'boolean',
        ]);

        TeamAnnouncementTemplate::create([
            'guild_id' => $guildModel->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'embed' => $validated['embed'] ?? null,
            'enabled' => $validated['enabled'] ?? true,
        ]);

        return back()->with('success', 'Template erfolgreich erstellt!');
    }

    /**
     * Update Team Announcement Template
     */
    public function updateTeamAnnouncementTemplate(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $template = TeamAnnouncementTemplate::where('guild_id', $guildModel->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:join,leave,upgrade,downgrade,custom',
            'embed' => 'nullable|array',
            'enabled' => 'boolean',
        ]);

        $template->update($validated);

        return back()->with('success', 'Template erfolgreich aktualisiert!');
    }

    /**
     * Delete Team Announcement Template
     */
    public function deleteTeamAnnouncementTemplate(Request $request, $guild, $id)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild || !$this->canManageGuild($userGuild->permissions)) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        $template = TeamAnnouncementTemplate::where('guild_id', $guildModel->id)->findOrFail($id);
        $template->delete();

        return back()->with('success', 'Template erfolgreich gelöscht!');
    }

    /**
     * Send team announcement
     */
    private function sendTeamAnnouncement($guildId, $channelId, $template, $userId, $roleId = null)
    {
        $botToken = config('services.discord.bot_token');
        if (!$botToken) {
            \Log::error('Bot Token fehlt beim Senden der Ankündigung');
            return;
        }

        if (!$template->embed || empty($template->embed)) {
            \Log::warning('Template hat kein Embed:', ['template_id' => $template->id]);
            return;
        }

        try {
            // Ersetze Platzhalter im Embed
            $embed = $template->embed;
            
            // Hole Benutzer-Daten
            $userResponse = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->timeout(5)->get("https://discord.com/api/v10/users/{$userId}");

            $userData = $userResponse->successful() ? $userResponse->json() : null;
            $userMention = $userData ? "<@{$userId}>" : "User {$userId}";
            $userName = $userData ? ($userData['username'] ?? 'Unknown') : 'Unknown';

            // Hole Rollen-Daten, falls vorhanden
            $roleName = null;
            if ($roleId) {
                $rolesResponse = Http::withHeaders([
                    'Authorization' => 'Bot ' . $botToken,
                ])->timeout(5)->get("https://discord.com/api/v10/guilds/{$guildId}/roles");
                
                if ($rolesResponse->successful()) {
                    $roles = $rolesResponse->json();
                    $role = collect($roles)->firstWhere('id', $roleId);
                    $roleName = $role ? $role['name'] : null;
                }
            }

            // Ersetze Platzhalter
            $replacements = [
                '{user}' => $userMention,
                '{username}' => $userName,
                '{role}' => $roleName ? "<@&{$roleId}>" : '',
                '{rolename}' => $roleName ?? '',
            ];

            if (isset($embed['title'])) {
                $embed['title'] = str_replace(array_keys($replacements), array_values($replacements), $embed['title']);
            }
            if (isset($embed['description'])) {
                $embed['description'] = str_replace(array_keys($replacements), array_values($replacements), $embed['description']);
            }

            // Stelle sicher, dass die Embed-Farbe korrekt formatiert ist
            if (isset($embed['color']) && is_string($embed['color']) && strpos($embed['color'], '#') === 0) {
                $embed['color'] = hexdec(substr($embed['color'], 1));
            }

            // Sende Embed
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post(
                "https://discord.com/api/v10/channels/{$channelId}/messages",
                ['embeds' => [$embed]]
            );

            if (!$response->successful()) {
                \Log::error('Fehler beim Senden der Ankündigung an Discord:', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'channel_id' => $channelId,
                    'guild_id' => $guildId
                ]);
            } else {
                \Log::info('Ankündigung erfolgreich gesendet:', [
                    'channel_id' => $channelId,
                    'template_id' => $template->id
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Exception beim Senden der Ankündigung:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Update team list in Discord channel
     */
    private function updateTeamList($guildId, $guildModel)
    {
        $teamConfig = $guildModel->teamManagementConfig;
        
        if (!$teamConfig || !$teamConfig->team_list_channel_id) {
            return;
        }

        $botToken = config('services.discord.bot_token');
        if (!$botToken) {
            return;
        }

        try {
            // Hole Discord-Rollen für Namen
            $rolesResponse = Http::withHeaders([
                'Authorization' => 'Bot ' . $botToken,
            ])->timeout(5)->get("https://discord.com/api/v10/guilds/{$guildId}/roles");
            
            $discordRoles = [];
            if ($rolesResponse->successful()) {
                $discordRoles = $rolesResponse->json();
            }

            // Lade alle Team-Ränge
            $teamRanks = TeamRank::where('guild_id', $guildModel->id)
                ->where('visible', true)
                ->orderBy('sort_order')
                ->get();

            // Hole alle Team-Mitglieder aus der Datenbank
            $allTeamMembers = TeamMember::where('guild_id', $guildModel->id)
                ->get();

            // Cache für Mitglieder-Rollen (um API-Calls zu reduzieren)
            $memberRolesCache = [];

            // Hole Rollen für alle Team-Mitglieder (mit Rate-Limit-Schutz)
            foreach ($allTeamMembers as $member) {
                try {
                    // Kleine Verzögerung, um Rate Limits zu vermeiden
                    usleep(100000); // 0.1 Sekunde
                    
                    $memberResponse = Http::withHeaders([
                        'Authorization' => 'Bot ' . $botToken,
                    ])->timeout(5)->get("https://discord.com/api/v10/guilds/{$guildId}/members/{$member->user_id}");
                    
                    if ($memberResponse->successful()) {
                        $memberData = $memberResponse->json();
                        $memberRolesCache[$member->user_id] = $memberData['roles'] ?? [];
                    }
                } catch (\Exception $e) {
                    \Log::warning('Fehler beim Abrufen der Rollen für Mitglied in Team-Liste:', [
                        'user_id' => $member->user_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Baue Embed mit Team-Liste
            $fields = [];
            foreach ($teamRanks as $rank) {
                if (!$rank->role_id) {
                    continue; // Überspringe Ränge ohne Discord-Rolle
                }

                // Hole Rollenname von Discord
                $roleName = $rank->name;
                $discordRole = collect($discordRoles)->firstWhere('id', $rank->role_id);
                if ($discordRole) {
                    $roleName = $discordRole['name'];
                }

                // Finde alle Mitglieder, die diese Rolle haben
                $membersWithRole = $allTeamMembers->filter(function($member) use ($rank, $memberRolesCache) {
                    $userRoles = $memberRolesCache[$member->user_id] ?? [];
                    return in_array($rank->role_id, $userRoles);
                })->unique('user_id');

                if ($membersWithRole->isEmpty()) {
                    // Zeige Rang auch ohne Mitglieder an
                    $fields[] = [
                        'name' => $roleName,
                        'value' => '*Hier sind keine Teammitglieder vorhanden.*',
                        'inline' => false,
                    ];
                } else {
                    $memberList = $membersWithRole->map(function($member) {
                        return "<@{$member->user_id}>";
                    })->join(', ');

                    // Teile lange Listen auf (Discord Embed Field Limit: 1024 Zeichen)
                    if (strlen($memberList) > 1024) {
                        $chunks = str_split($memberList, 1000);
                        foreach ($chunks as $index => $chunk) {
                            $fieldName = $index === 0 ? $roleName : '...';
                            $fields[] = [
                                'name' => $fieldName,
                                'value' => $chunk,
                                'inline' => false,
                            ];
                        }
                    } else {
                        $fields[] = [
                            'name' => $roleName,
                            'value' => $memberList,
                            'inline' => false,
                        ];
                    }
                }
            }

            if (empty($fields)) {
                $fields[] = [
                    'name' => 'Keine Ränge',
                    'value' => '*Es sind noch keine Team-Ränge vorhanden.*',
                    'inline' => false,
                ];
            }

            $embed = [
                'title' => 'Team-Liste',
                'description' => 'Aktuelle Übersicht aller Team-Mitglieder',
                'color' => hexdec('5865f2'),
                'fields' => $fields,
                'timestamp' => date('c'),
            ];

            // Wenn bereits eine Nachricht existiert, aktualisiere sie
            if ($teamConfig->team_list_message_id) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bot ' . $botToken,
                    'Content-Type' => 'application/json',
                ])->timeout(10)->patch(
                    "https://discord.com/api/v10/channels/{$teamConfig->team_list_channel_id}/messages/{$teamConfig->team_list_message_id}",
                    ['embeds' => [$embed]]
                );

                if (!$response->successful()) {
                    // Wenn Update fehlschlägt (z.B. Nachricht gelöscht), sende neue
                    if ($response->status() === 404) {
                        $this->sendNewTeamList($botToken, $teamConfig->team_list_channel_id, $embed, $guildModel);
                    } else {
                        \Log::error('Fehler beim Aktualisieren der Team-Liste:', [
                            'status' => $response->status(),
                            'body' => $response->body()
                        ]);
                    }
                }
            } else {
                // Sende neue Nachricht
                $this->sendNewTeamList($botToken, $teamConfig->team_list_channel_id, $embed, $guildModel);
            }
        } catch (\Exception $e) {
            \Log::error('Exception beim Aktualisieren der Team-Liste:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send new team list message
     */
    private function sendNewTeamList($botToken, $channelId, $embed, $guildModel)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bot ' . $botToken,
            'Content-Type' => 'application/json',
        ])->timeout(10)->post(
            "https://discord.com/api/v10/channels/{$channelId}/messages",
            ['embeds' => [$embed]]
        );

        if ($response->successful()) {
            $messageData = $response->json();
            $messageId = $messageData['id'] ?? null;
            
            if ($messageId) {
                $teamConfig = $guildModel->teamManagementConfig;
                $teamConfig->update(['team_list_message_id' => $messageId]);
            }
        } else {
            \Log::error('Fehler beim Senden der Team-Liste:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
    }

    /** Embed Sender: Speichern (max 5 pro Guild) */
    public function storeSavedEmbed(Request $request, $guild)
    {
        $result = $this->authorizeAndGetGuild($guild);
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $guildModel = $result['guildModel'];
        $count = $guildModel->savedEmbeds()->count();
        if ($count >= 5) {
            return back()->with('error', 'Maximal 5 Embeds pro Server speicherbar.');
        }
        $validated = $request->validate(array_merge([
            'name' => 'required|string|max:100',
            'content' => 'nullable|string|max:2000',
        ], $this->savedEmbedValidationRules()));
        $embeds = array_map(fn ($e) => $this->normalizeSavedEmbedPayload($e), $validated['embed']['embeds']);
        $guildModel->savedEmbeds()->create([
            'name' => $validated['name'],
            'content' => $validated['content'] ?? null,
            'embed' => ['embeds' => $embeds],
        ]);
        return back()->with('success', 'Embed gespeichert.');
    }

    /** Embed Sender: Aktualisieren */
    public function updateSavedEmbed(Request $request, $guild, $id)
    {
        $result = $this->authorizeAndGetGuild($guild);
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $guildModel = $result['guildModel'];
        $saved = $guildModel->savedEmbeds()->findOrFail($id);
        $validated = $request->validate(array_merge([
            'name' => 'required|string|max:100',
            'content' => 'nullable|string|max:2000',
        ], $this->savedEmbedValidationRules()));
        $embeds = array_map(fn ($e) => $this->normalizeSavedEmbedPayload($e), $validated['embed']['embeds']);
        $saved->update([
            'name' => $validated['name'],
            'content' => $validated['content'] ?? null,
            'embed' => ['embeds' => $embeds],
        ]);
        return back()->with('success', 'Embed aktualisiert.');
    }

    /** Embed Sender: Löschen */
    public function deleteSavedEmbed(Request $request, $guild, $id)
    {
        $result = $this->authorizeAndGetGuild($guild);
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $guildModel = $result['guildModel'];
        $saved = $guildModel->savedEmbeds()->findOrFail($id);
        $saved->delete();
        return back()->with('success', 'Embed gelöscht.');
    }

    /** Embed Sender: In Channel senden */
    public function sendSavedEmbed(Request $request, $guild)
    {
        $result = $this->authorizeAndGetGuild($guild);
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $validated = $request->validate(array_merge([
            'channel_id' => 'required|string',
            'content' => 'nullable|string|max:2000',
        ], $this->savedEmbedValidationRules()));
        $discordEmbeds = array_filter(array_map(
            fn ($e) => $this->buildDiscordEmbedFromSaved($e),
            $validated['embed']['embeds']
        ));
        $payload = [];
        if (!empty(trim($validated['content'] ?? ''))) {
            $payload['content'] = $validated['content'];
        }
        if (!empty($discordEmbeds)) {
            $payload['embeds'] = array_values($discordEmbeds);
        }
        if (empty($payload)) {
            return back()->with('error', 'Nachricht oder mindestens ein Embed erforderlich.');
        }
        $botToken = config('services.discord.bot_token');
        if (!$botToken) {
            return back()->with('error', 'Bot Token nicht konfiguriert.');
        }
        $response = Http::withHeaders([
            'Authorization' => 'Bot ' . $botToken,
            'Content-Type' => 'application/json',
        ])->timeout(10)->post(
            'https://discord.com/api/v10/channels/' . $validated['channel_id'] . '/messages',
            $payload
        );
        if (!$response->successful()) {
            $err = $response->json();
            $msg = $err['message'] ?? 'Unbekannter Fehler';
            return back()->with('error', 'Fehler beim Senden: ' . $msg);
        }
        return back()->with('success', 'Nachricht gesendet!');
    }

    /** Validierungs-Regeln für Embed-Sender (embeds-Array). */
    private function savedEmbedValidationRules(): array
    {
        return [
            'embed' => 'required|array',
            'embed.embeds' => 'required|array|max:10',
            'embed.embeds.*.title' => 'nullable|string|max:256',
            'embed.embeds.*.description' => 'nullable|string|max:4096',
            'embed.embeds.*.color' => 'nullable|string|max:20',
            'embed.embeds.*.image_url' => 'nullable|url|max:500',
            'embed.embeds.*.thumbnail_url' => 'nullable|url|max:500',
            'embed.embeds.*.footer_text' => 'nullable|string|max:2048',
            'embed.embeds.*.footer_icon_url' => 'nullable|url|max:500',
            'embed.embeds.*.footer_timestamp' => 'nullable|boolean',
            'embed.embeds.*.timestamp_value' => 'nullable|string|max:50',
            'embed.embeds.*.fields' => 'nullable|array',
            'embed.embeds.*.fields.*.name' => 'required_with:embed.embeds.*.fields|string|max:256',
            'embed.embeds.*.fields.*.value' => 'required_with:embed.embeds.*.fields|string|max:1024',
            'embed.embeds.*.fields.*.inline' => 'nullable|boolean',
        ];
    }

    private function normalizeSavedEmbedPayload(array $embed): array
    {
        $out = [
            'title' => $embed['title'] ?? null,
            'description' => $embed['description'] ?? null,
            'color' => $embed['color'] ?? null,
            'image_url' => $embed['image_url'] ?? null,
            'thumbnail_url' => $embed['thumbnail_url'] ?? null,
            'footer_text' => $embed['footer_text'] ?? null,
            'footer_icon_url' => $embed['footer_icon_url'] ?? null,
            'footer_timestamp' => $embed['footer_timestamp'] ?? null,
            'timestamp_value' => $embed['timestamp_value'] ?? null,
            'fields' => $embed['fields'] ?? [],
        ];
        return array_filter($out, fn ($v) => $v !== null && $v !== '');
    }

    private function buildDiscordEmbedFromSaved(array $e): array
    {
        $embed = [];
        if (!empty($e['title'])) {
            $embed['title'] = $e['title'];
        }
        if (!empty($e['description'])) {
            $embed['description'] = $e['description'];
        }
        if (!empty($e['color'])) {
            $hex = preg_replace('/^#/', '', $e['color']);
            $embed['color'] = strlen($hex) === 6 ? hexdec($hex) : 0;
        }
        if (!empty($e['image_url'])) {
            $embed['image'] = ['url' => $e['image_url']];
        }
        if (!empty($e['thumbnail_url'])) {
            $embed['thumbnail'] = ['url' => $e['thumbnail_url']];
        }
        if (!empty($e['footer_text'])) {
            $embed['footer'] = ['text' => $e['footer_text']];
            if (!empty($e['footer_icon_url'])) {
                $embed['footer']['icon_url'] = $e['footer_icon_url'];
            }
        }
        if (!empty($e['footer_timestamp'])) {
            $ts = !empty($e['timestamp_value']) ? $e['timestamp_value'] : null;
            if ($ts) {
                try {
                    $embed['timestamp'] = (new \DateTime($ts))->format('c');
                } catch (\Exception $ex) {
                    $embed['timestamp'] = now()->toIso8601String();
                }
            } else {
                $embed['timestamp'] = now()->toIso8601String();
            }
        }
        if (!empty($e['fields']) && is_array($e['fields'])) {
            $embed['fields'] = array_map(function ($f) {
                return [
                    'name' => $f['name'] ?? '',
                    'value' => $f['value'] ?? '',
                    'inline' => (bool) ($f['inline'] ?? false),
                ];
            }, $e['fields']);
        }
        return $embed;
    }

}

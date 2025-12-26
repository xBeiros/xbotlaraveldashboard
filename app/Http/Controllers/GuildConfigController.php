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
use Illuminate\Support\Facades\Http;

class GuildConfigController extends Controller
{
    public function updateWelcome(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
        $guildModel->welcomeConfig()->updateOrCreate(
            ['guild_id' => $guildModel->id],
            [
                'enabled' => $request->enabled ?? false,
                'channel_id' => $request->channel_id,
                'message' => $request->message,
                'use_embed' => $request->use_embed ?? false,
                'embed_title' => $request->embed_title,
                'embed_description' => $request->embed_description,
                'embed_color' => $request->embed_color,
                'embed_thumbnail' => $request->embed_thumbnail,
                'embed_image' => $request->embed_image,
                'embed_footer' => $request->embed_footer ?? true,
                'use_welcome_card' => $request->use_welcome_card ?? false,
                'card_font' => $request->card_font,
                'card_text_color' => $request->card_text_color,
                'card_background_color' => $request->card_background_color,
                'card_overlay_opacity' => $request->card_overlay_opacity ?? 50,
                'card_background_image' => $request->card_background_image,
                'card_title' => $request->card_title,
                'card_avatar_position' => $request->card_avatar_position ?? 'top',
            ]
        );

        return back()->with('success', 'Willkommensgrüße erfolgreich gespeichert!');
    }

    public function updateGoodbye(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
        $guildModel->goodbyeConfig()->updateOrCreate(
            ['guild_id' => $guildModel->id],
            [
                'enabled' => $request->enabled ?? false,
                'channel_id' => $request->channel_id,
                'message' => $request->message,
                'use_embed' => $request->use_embed ?? false,
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
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
        
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
            \Log::error('Discord API Error:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Fehler beim Senden der Nachricht: ' . $response->status());
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
        ]);

        $guildModel->update([
            'language' => $validated['language'],
        ]);

        return back()->with('success', 'Server-Einstellungen erfolgreich gespeichert!');
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
                'body' => $response->body(),
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

    public function updateBotPersonalization(Request $request, $guild)
    {
        $user = Auth::user();
        $userGuild = UserGuild::where('user_id', $user->id)
            ->where('guild_id', $guild)
            ->first();

        if (!$userGuild) {
            return redirect()->route('dashboard')->with('error', 'Kein Zugriff auf diesen Server.');
        }

        $botToken = config('services.discord.bot_token');
        $botClientId = config('services.discord.bot_client_id');
        
        if (!$botToken) {
            return back()->with('error', 'Bot Token nicht konfiguriert!');
        }
        
        if (!$botClientId) {
            return back()->with('error', 'Bot Client ID nicht konfiguriert!');
        }

        try {
            $guildModel = Guild::where('discord_id', $guild)->firstOrFail();
            $guildPayload = []; // Für Nickname (server-spezifisch) - Discord API
            $updateData = []; // Für Avatar/Banner und Nickname in DB (server-spezifisch)

            // Bot-Name ändern (nur für diesen Server - Nickname)
            if ($request->has('username')) {
                $nickname = $request->input('username');
                $nicknameTrimmed = trim($nickname);
                
                // Speichere Nickname in DB (auch wenn Discord API fehlschlägt)
                if (empty($nicknameTrimmed)) {
                    $updateData['bot_nickname'] = null; // Entfernt den Nickname
                    $guildPayload['nick'] = null; // Entfernt den Nickname in Discord
                } else {
                    $updateData['bot_nickname'] = $nicknameTrimmed;
                    $guildPayload['nick'] = $nicknameTrimmed;
                }
            }

            // Avatar ändern (server-spezifisch in DB speichern)
            if ($request->hasFile('avatar')) {
                $avatarFile = $request->file('avatar');
                \Log::info('[Bot Personalization] Avatar-Datei empfangen', [
                    'guild' => $guild,
                    'file_size' => $avatarFile->getSize(),
                    'file_mime' => $avatarFile->getMimeType(),
                    'file_name' => $avatarFile->getClientOriginalName(),
                ]);
                
                if ($avatarFile->isValid() && $avatarFile->getSize() <= 8 * 1024 * 1024) {
                    // Speichere Avatar server-spezifisch
                    $avatarPath = $avatarFile->store("guilds/{$guildModel->id}/bot", 'public');
                    $updateData['bot_avatar'] = $avatarPath;
                    
                    \Log::info('[Bot Personalization] Avatar gespeichert', [
                        'guild' => $guild,
                        'guild_model_id' => $guildModel->id,
                        'avatar_path' => $avatarPath,
                        'storage_url' => \Storage::disk('public')->url($avatarPath),
                    ]);
                } else {
                    \Log::warning('[Bot Personalization] Avatar-Datei ungültig oder zu groß', [
                        'guild' => $guild,
                        'file_size' => $avatarFile->getSize(),
                        'is_valid' => $avatarFile->isValid(),
                    ]);
                    return back()->with('error', 'Avatar-Datei ist zu groß (max. 8MB) oder ungültig.');
                }
            }

            // Banner ändern (server-spezifisch in DB speichern)
            if ($request->hasFile('banner')) {
                $bannerFile = $request->file('banner');
                \Log::info('[Bot Personalization] Banner-Datei empfangen', [
                    'guild' => $guild,
                    'file_size' => $bannerFile->getSize(),
                    'file_mime' => $bannerFile->getMimeType(),
                    'file_name' => $bannerFile->getClientOriginalName(),
                ]);
                
                if ($bannerFile->isValid() && $bannerFile->getSize() <= 8 * 1024 * 1024) {
                    // Speichere Banner server-spezifisch
                    $bannerPath = $bannerFile->store("guilds/{$guildModel->id}/bot", 'public');
                    $updateData['bot_banner'] = $bannerPath;
                    
                    \Log::info('[Bot Personalization] Banner gespeichert', [
                        'guild' => $guild,
                        'guild_model_id' => $guildModel->id,
                        'banner_path' => $bannerPath,
                        'storage_url' => \Storage::disk('public')->url($bannerPath),
                    ]);
                } else {
                    \Log::warning('[Bot Personalization] Banner-Datei ungültig oder zu groß', [
                        'guild' => $guild,
                        'file_size' => $bannerFile->getSize(),
                        'is_valid' => $bannerFile->isValid(),
                    ]);
                    return back()->with('error', 'Banner-Datei ist zu groß (max. 8MB) oder ungültig.');
                }
            }

            // Avatar/Banner entfernen
            if ($request->has('remove_avatar') && $request->input('remove_avatar')) {
                if ($guildModel->bot_avatar) {
                    \Storage::disk('public')->delete($guildModel->bot_avatar);
                }
                $updateData['bot_avatar'] = null;
            }

            if ($request->has('remove_banner') && $request->input('remove_banner')) {
                if ($guildModel->bot_banner) {
                    \Storage::disk('public')->delete($guildModel->bot_banner);
                }
                $updateData['bot_banner'] = null;
            }

            if (empty($guildPayload) && empty($updateData)) {
                return back()->with('error', 'Keine Änderungen vorgenommen.');
            }

            $errors = [];

            // 1. Speichere Nickname, Avatar/Banner server-spezifisch in DB (immer, auch wenn Discord API fehlschlägt)
            if (!empty($updateData)) {
                \Log::info('[Bot Personalization] Aktualisiere Datenbank', [
                    'guild' => $guild,
                    'update_data' => $updateData,
                ]);
                
                $guildModel->update($updateData);
                
                \Log::info('[Bot Personalization] Datenbank aktualisiert', [
                    'guild' => $guild,
                    'bot_avatar' => $guildModel->fresh()->bot_avatar,
                    'bot_banner' => $guildModel->fresh()->bot_banner,
                    'bot_nickname' => $guildModel->fresh()->bot_nickname,
                ]);
            }

            // 2. Rufe Bot-API auf, um Server-Nickname sofort zu aktualisieren
            // Der Bot kann seinen eigenen Nickname über Discord.js ändern (benötigt CHANGE_NICKNAME)
            if (isset($updateData['bot_nickname'])) {
                $botApiUrl = config('services.discord.bot_api_url', 'http://localhost:3001');
                $botApiToken = config('services.discord.bot_api_token', 'change-me-in-production');
                
                try {
                    $botApiResponse = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $botApiToken,
                        'Content-Type' => 'application/json',
                    ])->timeout(5)->post("{$botApiUrl}/api/update-nickname", [
                        'guildId' => $guild,
                        'nickname' => $updateData['bot_nickname'],
                    ]);
                    
                    if ($botApiResponse->successful()) {
                        \Log::info('Bot-API: Server-Nickname erfolgreich aktualisiert', [
                            'guild' => $guild,
                            'nickname' => $updateData['bot_nickname'],
                        ]);
                    } else {
                        \Log::warning('Bot-API: Fehler beim Aktualisieren des Server-Nicknames', [
                            'guild' => $guild,
                            'status' => $botApiResponse->status(),
                            'response' => $botApiResponse->json(),
                        ]);
                    }
                } catch (\Exception $e) {
                    // Ignoriere Fehler, da der Bot den Nickname automatisch alle 30 Sekunden aktualisiert
                    \Log::warning('Bot-API: Konnte nicht erreicht werden (Bot aktualisiert automatisch alle 30 Sekunden)', [
                        'guild' => $guild,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Lade die aktualisierten Daten neu, damit sie im Frontend angezeigt werden
            $guildModel->refresh();
            
            // Lade Bot-Info neu mit aktualisiertem Nickname
            $botInfo = null;
            if ($botToken && $botClientId) {
                try {
                    $botResponse = Http::withHeaders([
                        'Authorization' => 'Bot ' . $botToken,
                    ])->timeout(5)->get('https://discord.com/api/v10/users/@me');
                    
                    if ($botResponse->successful()) {
                        $botData = $botResponse->json();
                        $serverAvatar = $guildModel->bot_avatar ? \Storage::disk('public')->url($guildModel->bot_avatar) : null;
                        $serverBanner = $guildModel->bot_banner ? \Storage::disk('public')->url($guildModel->bot_banner) : null;
                        $serverNickname = $guildModel->bot_nickname;
                        
                        \Log::info('[Bot Personalization] Bot-Info geladen', [
                            'guild' => $guild,
                            'bot_id' => $botData['id'] ?? null,
                            'server_avatar' => $serverAvatar,
                            'server_banner' => $serverBanner,
                            'server_nickname' => $serverNickname,
                            'global_avatar' => isset($botData['avatar']) ? "https://cdn.discordapp.com/avatars/{$botData['id']}/{$botData['avatar']}.png" : null,
                        ]);
                        
                        $botInfo = [
                            'id' => $botData['id'] ?? null,
                            'username' => $serverNickname ?? $botData['username'] ?? null,
                            'avatar' => $serverAvatar ?? (isset($botData['avatar']) ? "https://cdn.discordapp.com/avatars/{$botData['id']}/{$botData['avatar']}.png" : null),
                            'banner' => $serverBanner ?? (isset($botData['banner']) ? "https://cdn.discordapp.com/banners/{$botData['id']}/{$botData['banner']}.png" : null),
                        ];
                    } else {
                        \Log::warning('[Bot Personalization] Fehler beim Abrufen der Bot-Info', [
                            'guild' => $guild,
                            'status' => $botResponse->status(),
                            'response' => $botResponse->json(),
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::warning("Fehler beim Abrufen der Bot-Info nach Update: " . $e->getMessage());
                }
            }

            if (!empty($errors)) {
                return back()->with('error', 'Fehler beim Aktualisieren: ' . implode(' | ', $errors));
            }

            // Lade die Seite neu, damit botInfo korrekt aktualisiert wird
            return redirect()->route('guild.server-management', ['guild' => $guild])
                ->with('success', 'Server-Profil erfolgreich aktualisiert!');
        } catch (\Exception $e) {
            \Log::error('Fehler bei Bot-Personalisierung:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Fehler beim Aktualisieren des Bot-Profils: ' . $e->getMessage());
        }
    }
}

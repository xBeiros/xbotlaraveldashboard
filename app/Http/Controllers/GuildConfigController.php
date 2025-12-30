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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            'timezone' => 'required|string|max:50',
        ]);

        $guildModel->update([
            'language' => $validated['language'],
            'timezone' => $validated['timezone'],
        ]);

        return back()->with('success', 'Server-Einstellungen erfolgreich gespeichert!');
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
            'mode' => 'required|in:count,time',
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
            // Bereite Daten für Bot vor
            $deleteData = [
                'guild_id' => $guild,
                'channel_id' => $validated['channel_id'],
                'mode' => $validated['mode'],
                'count' => $validated['count'] ?? null,
                'time_hours' => $validated['timeHours'] ?? 0,
                'time_minutes' => $validated['timeMinutes'] ?? 0,
                'send_notification' => $validated['sendNotification'] ?? false,
                'notification_title' => $validated['notificationTitle'] ?? null,
                'notification_description' => $validated['notificationDescription'] ?? null,
                'notification_color' => $validated['notificationColor'] ?? '#5865f2',
                'show_footer' => $validated['showFooter'] ?? true,
                'user_id' => $user->discord_id,
            ];

            // Sende Request an Bot (über HTTP oder Queue)
            // Hier verwenden wir einen HTTP-Request an den Bot
            // Der Bot muss einen Endpoint haben, der diese Anfrage verarbeitet
            // Alternativ: Verwende Discord API direkt (aber das ist komplizierter wegen Rate Limits)
            
            // Für jetzt: Sende direkt über Discord API
            // Hole alle Nachrichten und lösche sie
            $channelId = $validated['channel_id'];
            $deletedCount = 0;
            
            if ($validated['mode'] === 'count') {
                // Lösche die letzten X Nachrichten
                $limit = min($validated['count'], 100); // Discord limit: max 100 pro Request
                
                $response = Http::withHeaders([
                    'Authorization' => 'Bot ' . $botToken,
                ])->timeout(10)->get("https://discord.com/api/v10/channels/{$channelId}/messages?limit={$limit}");
                
                if ($response->successful()) {
                    $messages = $response->json();
                    
                    // Lösche Nachrichten in Batches (max 100 pro Request)
                    $messageIds = array_column($messages, 'id');
                    
                    if (!empty($messageIds)) {
                        // Discord Bulk Delete API (max 100 Nachrichten, min 2, max 14 Tage alt)
                        // Für einzelne Nachrichten verwenden wir DELETE /channels/{channel.id}/messages/{message.id}
                        foreach ($messageIds as $messageId) {
                            try {
                                $deleteResponse = Http::withHeaders([
                                    'Authorization' => 'Bot ' . $botToken,
                                ])->timeout(5)->delete("https://discord.com/api/v10/channels/{$channelId}/messages/{$messageId}");
                                
                                if ($deleteResponse->successful()) {
                                    $deletedCount++;
                                }
                                
                                // Rate limit: 5 requests per second
                                usleep(200000); // 0.2 seconds delay
                            } catch (\Exception $e) {
                                \Log::warning("Fehler beim Löschen der Nachricht {$messageId}: " . $e->getMessage());
                            }
                        }
                    }
                }
            } else {
                // Lösche Nachrichten nach Zeitraum
                $cutoffTime = now()->subHours($validated['timeHours'] ?? 0)->subMinutes($validated['timeMinutes'] ?? 0);
                $cutoffTimestamp = $cutoffTime->timestamp * 1000; // Discord verwendet Millisekunden
                
                $lastId = null;
                $allMessages = [];
                
                // Hole alle Nachrichten (bis zu 1000)
                while (count($allMessages) < 1000) {
                    $url = "https://discord.com/api/v10/channels/{$channelId}/messages?limit=100";
                    if ($lastId) {
                        $url .= "&before={$lastId}";
                    }
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Bot ' . $botToken,
                    ])->timeout(10)->get($url);
                    
                    if (!$response->successful() || empty($response->json())) {
                        break;
                    }
                    
                    $messages = $response->json();
                    $filteredMessages = array_filter($messages, function($msg) use ($cutoffTimestamp) {
                        return (int)$msg['timestamp'] >= $cutoffTimestamp;
                    });
                    
                    if (empty($filteredMessages)) {
                        break; // Keine Nachrichten mehr im Zeitraum
                    }
                    
                    $allMessages = array_merge($allMessages, array_values($filteredMessages));
                    $lastId = end($messages)['id'];
                    
                    // Wenn die letzte Nachricht älter ist als der Cutoff, stoppe
                    if ((int)end($messages)['timestamp'] < $cutoffTimestamp) {
                        break;
                    }
                    
                    usleep(200000); // Rate limit
                }
                
                // Lösche alle gefundenen Nachrichten
                foreach ($allMessages as $message) {
                    try {
                        $deleteResponse = Http::withHeaders([
                            'Authorization' => 'Bot ' . $botToken,
                        ])->timeout(5)->delete("https://discord.com/api/v10/channels/{$channelId}/messages/{$message['id']}");
                        
                        if ($deleteResponse->successful()) {
                            $deletedCount++;
                        }
                        
                        usleep(200000); // Rate limit
                    } catch (\Exception $e) {
                        \Log::warning("Fehler beim Löschen der Nachricht {$message['id']}: " . $e->getMessage());
                    }
                }
            }
            
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

        $validated = $request->validate([
            'title' => 'required|string|max:256',
            'description' => 'nullable|string|max:2000',
            'channel_id' => 'required|string',
            'prize_type' => 'required|in:code,role,custom',
            'prize_code' => 'nullable|string|max:255|required_if:prize_type,code',
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

            if ($validated['prize_type'] === 'code' && $validated['prize_code']) {
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
                return back()->with('error', 'Fehler beim Senden des Giveaways: ' . $response->body());
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
                'prize_code' => $validated['prize_code'] ?? null,
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

    /**
     * Prüft ob der Benutzer die Berechtigung hat, den Server zu verwalten
     */
    private function canManageGuild($permissions)
    {
        // Berechtigung: MANAGE_GUILD (0x20) oder Administrator (0x8)
        return ($permissions & 0x20) !== 0 || ($permissions & 0x8) !== 0;
    }

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

}

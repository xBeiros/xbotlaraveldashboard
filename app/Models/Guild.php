<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
    use HasFactory;

    protected $fillable = [
        'discord_id',
        'name',
        'icon',
        'owner_id',
        'bot_active',
        'prefix',
        'language',
        'ticket_transcript_enabled',
        'ticket_close_require_confirmation',
        'ticket_close_message',
        'ticket_close_confirmation_button_text',
        'timezone',
        'bot_avatar',
        'bot_banner',
        'bot_nickname',
    ];

    protected $casts = [
        'bot_active' => 'boolean',
        'ticket_transcript_enabled' => 'boolean',
        'ticket_close_require_confirmation' => 'boolean',
    ];

    public function welcomeConfig()
    {
        return $this->hasOne(WelcomeConfig::class);
    }

    public function goodbyeConfig()
    {
        return $this->hasOne(GoodbyeConfig::class);
    }

    public function socialNotifications()
    {
        return $this->hasMany(SocialNotification::class);
    }

    public function autoModerationConfig()
    {
        return $this->hasOne(AutoModerationConfig::class);
    }

    public function levelingConfig()
    {
        return $this->hasOne(LevelingConfig::class);
    }

    public function roleRewards()
    {
        return $this->hasMany(RoleReward::class);
    }

    public function rankCardConfig()
    {
        return $this->hasOne(RankCardConfig::class);
    }

    public function reactionRoles()
    {
        return $this->hasMany(ReactionRole::class);
    }

    public function ticketCategories()
    {
        return $this->hasMany(TicketCategory::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketPosts()
    {
        return $this->hasMany(TicketPost::class);
    }

    public function autoDeleteMessages()
    {
        return $this->hasMany(AutoDeleteMessage::class);
    }

    public function giveaways()
    {
        return $this->hasMany(Giveaway::class);
    }

    public function birthdays()
    {
        return $this->hasMany(Birthday::class);
    }

    public function birthdayConfig()
    {
        return $this->hasOne(BirthdayConfig::class);
    }

    public function addOns()
    {
        return $this->hasMany(AddOn::class);
    }

    public function teamRanks()
    {
        return $this->hasMany(TeamRank::class);
    }

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function teamManagementConfig()
    {
        return $this->hasOne(TeamManagementConfig::class);
    }

    public function teamAnnouncementTemplates()
    {
        return $this->hasMany(TeamAnnouncementTemplate::class);
    }

    public function statisticsConfig()
    {
        return $this->hasOne(GuildStatisticsConfig::class);
    }
}

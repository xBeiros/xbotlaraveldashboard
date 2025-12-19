<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LevelingConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'guild_id',
        'enabled',
        'xp_rate',
        'min_xp',
        'max_xp',
        'cooldown_seconds',
        'level_up_channel_id',
        'level_up_type',
        'level_up_message',
        'role_reward_type',
        'remove_role_on_xp_loss',
        'excluded_roles_type',
        'excluded_roles',
        'excluded_channels_type',
        'excluded_channels',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'xp_rate' => 'decimal:2',
        'min_xp' => 'integer',
        'max_xp' => 'integer',
        'cooldown_seconds' => 'integer',
        'remove_role_on_xp_loss' => 'boolean',
        'excluded_roles' => 'array',
        'excluded_channels' => 'array',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }

    public function roleRewards()
    {
        return $this->hasMany(RoleReward::class, 'guild_id', 'guild_id');
    }
}

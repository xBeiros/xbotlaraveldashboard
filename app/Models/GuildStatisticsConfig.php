<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuildStatisticsConfig extends Model
{
    protected $table = 'guild_statistics_config';

    protected $fillable = [
        'guild_id',
        'enabled',
        'channel_id',
        'message_id',
        'category_id',
        'channel_name',
        'channel_id_members',
        'channel_id_joins',
        'channel_id_leaves',
        'channel_id_vc',
        'channel_id_boosts',
        'channel_name_members',
        'channel_name_joins',
        'channel_name_leaves',
        'channel_name_vc',
        'channel_name_boosts',
        'stat_members',
        'stat_joins',
        'stat_leaves',
        'stat_vc',
        'stat_boosts',
        'update_interval_minutes',
        'joins_24h',
        'leaves_24h',
        'last_reset_at',
        'last_message_edit_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'stat_members' => 'boolean',
        'stat_joins' => 'boolean',
        'stat_leaves' => 'boolean',
        'stat_vc' => 'boolean',
        'stat_boosts' => 'boolean',
        'last_reset_at' => 'datetime',
        'last_message_edit_at' => 'datetime',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

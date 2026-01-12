<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamManagementConfig extends Model
{
    protected $fillable = [
        'guild_id',
        'channel_id',
        'default_role_id',
        'notify_join',
        'notify_leave',
        'notify_upgrade',
        'notify_downgrade',
        'join_embed',
        'leave_embed',
        'upgrade_embed',
        'downgrade_embed',
    ];

    protected $casts = [
        'notify_join' => 'boolean',
        'notify_leave' => 'boolean',
        'notify_upgrade' => 'boolean',
        'notify_downgrade' => 'boolean',
        'join_embed' => 'array',
        'leave_embed' => 'array',
        'upgrade_embed' => 'array',
        'downgrade_embed' => 'array',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

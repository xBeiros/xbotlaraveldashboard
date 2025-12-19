<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialNotification extends Model
{
    protected $fillable = [
        'guild_id',
        'platform',
        'channel_id',
        'username',
        'webhook_url',
        'enabled',
        'notify_live',
        'custom_message',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'notify_live' => 'boolean',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Giveaway extends Model
{
    protected $fillable = [
        'guild_id',
        'channel_id',
        'message_id',
        'title',
        'description',
        'prize_type',
        'prize_code',
        'prize_role_id',
        'prize_custom',
        'winner_count',
        'ends_at',
        'ended',
        'winner_message',
        'ticket_message',
    ];

    protected $casts = [
        'ends_at' => 'datetime',
        'ended' => 'boolean',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(GiveawayParticipant::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(GiveawayWinner::class);
    }
}

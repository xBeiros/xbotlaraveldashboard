<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiveawayWinner extends Model
{
    protected $fillable = [
        'giveaway_id',
        'user_id',
        'notified',
        'ticket_created',
    ];

    protected $casts = [
        'notified' => 'boolean',
        'ticket_created' => 'boolean',
    ];

    public function giveaway(): BelongsTo
    {
        return $this->belongsTo(Giveaway::class);
    }
}

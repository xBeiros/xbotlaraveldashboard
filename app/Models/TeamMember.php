<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    protected $fillable = [
        'guild_id',
        'rank_id',
        'user_id',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(TeamRank::class, 'rank_id');
    }
}

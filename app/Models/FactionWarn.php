<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactionWarn extends Model
{
    protected $table = 'faction_warns';

    protected $fillable = [
        'faction_id',
        'guild_id',
        'reason',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }

    /** Nur noch nicht abgelaufene Warns. */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faction extends Model
{
    protected $fillable = [
        'guild_id',
        'name',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }

    public function warns(): HasMany
    {
        return $this->hasMany(FactionWarn::class);
    }

    public function activeWarns(): HasMany
    {
        return $this->warns()->active();
    }
}

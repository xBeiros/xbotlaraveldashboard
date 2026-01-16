<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamAnnouncementTemplate extends Model
{
    protected $fillable = [
        'guild_id',
        'name',
        'type',
        'embed',
        'enabled',
    ];

    protected $casts = [
        'embed' => 'array',
        'enabled' => 'boolean',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

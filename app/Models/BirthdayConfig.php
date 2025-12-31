<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BirthdayConfig extends Model
{
    protected $fillable = [
        'guild_id',
        'enabled',
        'channel_id',
        'role_id',
        'embed_title',
        'embed_description',
        'embed_color',
        'embed_thumbnail',
        'embed_image',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

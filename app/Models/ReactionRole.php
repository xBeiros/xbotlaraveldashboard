<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReactionRole extends Model
{
    protected $fillable = [
        'guild_id',
        'message_id',
        'channel_id',
        'enabled',
        'embed_title',
        'embed_description',
        'embed_color',
        'embed_thumbnail',
        'embed_image',
        'embed_banner',
        'embed_footer',
        'reactions',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'embed_footer' => 'boolean',
        'reactions' => 'array',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

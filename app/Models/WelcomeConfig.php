<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelcomeConfig extends Model
{
    protected $fillable = [
        'guild_id',
        'channel_id',
        'message',
        'enabled',
        'use_embed',
        'embed_author',
        'embed_title',
        'embed_description',
        'embed_color',
        'embed_thumbnail',
        'embed_image',
        'embed_footer',
        'use_welcome_card',
        'card_font',
        'card_text_color',
        'card_background_color',
        'card_overlay_opacity',
        'card_background_image',
        'card_title',
        'card_avatar_position',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'use_embed' => 'boolean',
        'embed_author' => 'boolean',
        'embed_footer' => 'boolean',
        'use_welcome_card' => 'boolean',
        'card_overlay_opacity' => 'integer',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

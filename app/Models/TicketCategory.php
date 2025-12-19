<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'guild_id',
        'name',
        'emoji',
        'description',
        'welcome_message',
        'use_welcome_card',
        'card_background_type',
        'card_background_color',
        'card_background_image',
        'card_overlay_opacity',
        'card_text_color',
        'card_font',
        'card_avatar_position',
        'category_id',
        'channel_name_format',
        'supporter_roles',
        'enabled',
        'order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'supporter_roles' => 'array',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }
}

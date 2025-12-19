<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'guild_id',
        'channel_id',
        'message_id',
        'embed_title',
        'embed_description',
        'embed_color',
        'embed_image',
        'embed_banner',
        'embed_footer',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'embed_footer' => 'boolean',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

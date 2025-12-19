<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoModerationConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'guild_id',
        'bad_words_enabled',
        'bad_words_list',
        'bad_words_delete_message',
        'bad_words_warning_message',
        'bad_words_use_embed',
        'bad_words_embed_title',
        'bad_words_embed_description',
        'bad_words_embed_color',
        'bad_words_embed_footer',
        'block_discord_invites',
        'block_discord_invites_delete_message',
        'block_discord_invites_warning_message',
        'block_discord_invites_use_embed',
        'block_discord_invites_embed_title',
        'block_discord_invites_embed_description',
        'block_discord_invites_embed_color',
        'block_discord_invites_embed_footer',
        'whitelist_channels',
        'whitelist_roles',
    ];

    protected $casts = [
        'bad_words_enabled' => 'boolean',
        'bad_words_delete_message' => 'boolean',
        'bad_words_use_embed' => 'boolean',
        'bad_words_embed_footer' => 'boolean',
        'block_discord_invites' => 'boolean',
        'block_discord_invites_delete_message' => 'boolean',
        'block_discord_invites_use_embed' => 'boolean',
        'block_discord_invites_embed_footer' => 'boolean',
        'bad_words_list' => 'array',
        'whitelist_channels' => 'array',
        'whitelist_roles' => 'array',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

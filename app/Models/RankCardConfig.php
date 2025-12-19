<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankCardConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'guild_id',
        'background_type',
        'background_color',
        'background_image',
        'custom_background_url',
        'overlay_opacity',
        'text_color',
        'rank_text_color',
        'level_text_color',
        'xp_text_color',
        'progress_bar_color',
        'welcome_message',
    ];

    protected $casts = [
        'overlay_opacity' => 'integer',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

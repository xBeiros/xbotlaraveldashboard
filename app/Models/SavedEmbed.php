<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedEmbed extends Model
{
    protected $fillable = [
        'guild_id',
        'name',
        'content',
        'embed',
    ];

    protected $casts = [
        'embed' => 'array',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

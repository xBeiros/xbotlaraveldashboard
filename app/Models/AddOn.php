<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddOn extends Model
{
    protected $fillable = [
        'guild_id',
        'addon_type',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

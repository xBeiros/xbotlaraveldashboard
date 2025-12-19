<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGuild extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guild_id',
        'name',
        'icon',
        'owner',
        'permissions',
        'bot_joined',
    ];

    protected $casts = [
        'owner' => 'boolean',
        'bot_joined' => 'boolean',
        'permissions' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

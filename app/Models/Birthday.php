<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Birthday extends Model
{
    protected $fillable = [
        'guild_id',
        'user_id',
        'birthday',
    ];

    protected $casts = [
        'birthday' => 'date',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

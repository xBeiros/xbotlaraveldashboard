<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoDeleteMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'guild_id',
        'channel_id',
        'interval_minutes',
        'delete_count',
        'enabled',
        'last_run_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'interval_minutes' => 'integer',
        'delete_count' => 'integer',
        'last_run_at' => 'datetime',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactionManagementConfig extends Model
{
    protected $table = 'faction_management_configs';

    protected $fillable = [
        'guild_id',
        'channel_id_create',
        'channel_id_warn',
        'channel_id_dissolve',
        'channel_id_overview',
        'overview_message_id',
        'embed_create',
        'embed_warn',
        'embed_dissolve',
        'embed_overview',
        'warn_duration_days',
    ];

    protected $casts = [
        'embed_create' => 'array',
        'embed_warn' => 'array',
        'embed_dissolve' => 'array',
        'embed_overview' => 'array',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }
}

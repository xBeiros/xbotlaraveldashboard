<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'guild_id',
        'category_id',
        'channel_id',
        'user_id',
        'message_id',
        'status',
        'closed_at',
        'closed_by',
        'transcript',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public function guild(): BelongsTo
    {
        return $this->belongsTo(Guild::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(TicketMember::class);
    }
}

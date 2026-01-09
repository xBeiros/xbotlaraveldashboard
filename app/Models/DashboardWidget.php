<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guild_id',
        'widget_type',
        'position',
        'column',
        'row',
        'config',
        'enabled',
    ];

    protected $casts = [
        'config' => 'array',
        'enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

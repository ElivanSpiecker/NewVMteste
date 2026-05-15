<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YoutubeAccount extends Model
{
    protected $fillable = [
        'channel_id',
        'channel_title',
        'display_name',
        'access_token',
        'refresh_token',
        'expires_at',
        'scopes',
    ];

    protected $casts = [
        'access_token'  => 'encrypted',
        'refresh_token' => 'encrypted',
        'expires_at'    => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    public function uploads(): HasMany
    {
        return $this->hasMany(YoutubeUpload::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function expiresSoon(int $seconds = 60): bool
    {
        return $this->expires_at !== null && $this->expires_at->diffInSeconds(now()) <= $seconds;
    }
}

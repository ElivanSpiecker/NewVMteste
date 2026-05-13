<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YoutubePost extends Model
{
    protected $fillable = [
        'video_id',
        'title',
        'description',
        'tags',
        'category_id',
        'privacy_status',
        'scheduled_at',
        'status',
        'youtube_video_id',
        'error',
        'published_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'scheduled' => 'Agendado',
            'publishing' => 'Publicando',
            'published' => 'Publicado',
            'failed' => 'Falhou',
            default => $this->status,
        };
    }
}

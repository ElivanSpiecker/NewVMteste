<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YoutubeUpload extends Model
{
    protected $fillable = [
        'youtube_account_id',
        'video_id',
        'title',
        'description',
        'tags',
        'category_id',
        'privacy_status',
        'made_for_kids',
        'scheduled_at',
        'source_path',
        'status',
        'progresso',
        'youtube_video_id',
        'erro',
    ];

    protected $casts = [
        'scheduled_at'  => 'datetime',
        'made_for_kids' => 'boolean',
    ];

    protected $attributes = [
        'status'         => 'pending',
        'progresso'      => 0,
        'privacy_status' => 'public',
        'category_id'    => '22',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(YoutubeAccount::class, 'youtube_account_id');
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' || ($this->scheduled_at !== null && $this->scheduled_at->isFuture());
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isProcessing(): bool
    {
        return in_array($this->status, ['pending', 'uploading'], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'Na fila',
            'uploading' => 'Enviando ao YouTube...',
            'scheduled' => 'Agendado',
            'published' => 'Publicado',
            'failed'    => 'Falhou',
            default     => $this->status,
        };
    }

    public function tagsArray(): array
    {
        if (empty($this->tags)) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $this->tags))));
    }

    public function youtubeUrl(): ?string
    {
        return $this->youtube_video_id
            ? "https://www.youtube.com/shorts/{$this->youtube_video_id}"
            : null;
    }
}

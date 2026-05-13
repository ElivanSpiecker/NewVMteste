<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    protected $fillable = [
        'tema',
        'duracao',
        'status',
        'progresso',
        'erro',
        'video_path',
        'srt_path',
        'thumbnail_path',
    ];

    protected $attributes = [
        'status'    => 'pending',
        'progresso' => 0,
    ];

    public function youtubePosts(): HasMany
    {
        return $this->hasMany(YoutubePost::class);
    }

    public function isProcessing(): bool
    {
        return !in_array($this->status, ['done', 'failed', 'pending']);
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'               => 'Na fila',
            'generating_script'     => 'Gerando roteiro...',
            'generating_images'     => 'Gerando imagens...',
            'generating_narration'  => 'Gerando narração...',
            'generating_music'      => 'Gerando música...',
            'generating_subtitles'  => 'Gerando legendas...',
            'assembling'            => 'Montando vídeo...',
            'done'                  => 'Concluído',
            'failed'                => 'Falhou',
            default                 => $this->status,
        };
    }
}

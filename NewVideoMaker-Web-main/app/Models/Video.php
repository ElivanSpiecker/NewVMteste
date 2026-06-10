<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'tema',
        'duracao',
        'idioma',
        'voz',
        'imagens_modo',
        'narracao_modo',
        'musica_modo',
        'legendas_modo',
        'status',
        'progresso',
        'erro',
        'video_path',
        'srt_path',
        'thumbnail_path',
        'narracao_path',
        'musica_path',
        'imagens_paths',
    ];

    protected $attributes = [
        'status'    => 'pending',
        'progresso' => 0,
    ];

    protected $casts = [
        'imagens_paths' => 'array',
    ];

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
            'pending'               => __('Na fila'),
            'generating_script'     => __('Gerando roteiro...'),
            'generating_images'     => __('Gerando imagens...'),
            'generating_narration'  => __('Gerando narração...'),
            'generating_music'      => __('Gerando música...'),
            'generating_subtitles'  => __('Gerando legendas...'),
            'assembling'            => __('Montando vídeo...'),
            'done'                  => __('Concluído'),
            'failed'                => __('Falhou'),
            default                 => $this->status,
        };
    }
}

<?php

namespace App\Services;

use App\Jobs\GerarVideo;
use App\Models\Video;

class VideoRegenerationService
{
    /**
     * Cria um novo vídeo regenerando apenas as etapas selecionadas,
     * reaproveitando artefatos do vídeo de origem.
     *
     * @param  array  $keep  Quais etapas manter: ['imagens', 'narracao', 'musica']
     *                       (legendas sempre regeneram se narração mudar)
     * @param  array  $overrides  Campos a sobrescrever no novo vídeo (ex: ['voz' => '...'])
     */
    public function regenerate(Video $source, array $keep, array $overrides = []): Video
    {
        // Atributos base = clone do vídeo de origem
        $attributes = [
            'tema'           => $source->tema,
            'duracao'        => $source->duracao,
            'idioma'         => $source->idioma ?? 'PT-BR',
            'voz'            => $source->voz,
            'imagens_modo'   => 'gerar',
            'narracao_modo'  => 'gerar',
            'musica_modo'    => $source->musica_modo === 'nenhum' ? 'nenhum' : 'gerar',
            'legendas_modo'  => $source->legendas_modo === 'nenhum' ? 'nenhum' : 'gerar',
        ];

        // Aplica overrides (ex: voz nova)
        foreach (['tema', 'duracao', 'idioma', 'voz', 'musica_modo', 'legendas_modo'] as $field) {
            if (array_key_exists($field, $overrides)) {
                $attributes[$field] = $overrides[$field];
            }
        }

        // Se voz ou idioma mudaram, a narração antiga não serve mais — força regeração
        $narracaoForcadaRegerar =
            (array_key_exists('voz', $overrides)    && $overrides['voz']    !== $source->voz) ||
            (array_key_exists('idioma', $overrides) && $overrides['idioma'] !== ($source->idioma ?? 'PT-BR'));

        // Etapas mantidas viram modo=upload (e os arquivos serão copiados abaixo)
        $keepImagens  = in_array('imagens',  $keep, true) && !empty($source->imagens_paths);
        $keepNarracao = in_array('narracao', $keep, true) && $source->narracao_path && file_exists($source->narracao_path) && !$narracaoForcadaRegerar;
        $keepMusica   = in_array('musica',   $keep, true) && $source->musica_path   && file_exists($source->musica_path);

        if ($keepImagens) {
            $attributes['imagens_modo'] = 'upload';
        }
        if ($keepNarracao) {
            $attributes['narracao_modo'] = 'upload';
        }
        if ($keepMusica) {
            $attributes['musica_modo'] = 'upload';
        }

        $video = Video::create($attributes);

        $uploadDir = storage_path("app/uploads/{$video->id}");
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if ($keepImagens) {
            $destImg = "{$uploadDir}/imagens";
            if (!is_dir($destImg)) {
                mkdir($destImg, 0755, true);
            }
            foreach ($source->imagens_paths as $i => $src) {
                if (!file_exists($src)) {
                    continue;
                }
                $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION) ?: 'png');
                @copy($src, sprintf('%s/cena%02d.%s', $destImg, $i + 1, $ext));
            }
        }

        if ($keepNarracao) {
            $ext = strtolower(pathinfo($source->narracao_path, PATHINFO_EXTENSION) ?: 'wav');
            @copy($source->narracao_path, "{$uploadDir}/narracao.{$ext}");
        }

        if ($keepMusica) {
            $ext = strtolower(pathinfo($source->musica_path, PATHINFO_EXTENSION) ?: 'mp3');
            @copy($source->musica_path, "{$uploadDir}/musica.{$ext}");
        }

        GerarVideo::dispatch($video->id)->onQueue('video-generation');

        return $video;
    }
}

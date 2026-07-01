<?php

namespace App\Services;

use App\Jobs\GerarVideo;
use App\Models\Video;
use Illuminate\Http\Request;

class VideoCreationService
{
    public function __construct(private readonly VideoUploadHandler $uploadHandler) {}

    /**
     * Cria o registro do vídeo, processa uploads e dispara o Job de geração.
     */
    public function create(array $attributes, Request $request): Video
    {
        $video = Video::create($attributes);

        $this->uploadHandler->handleAll($video, $request, $attributes);

        GerarVideo::dispatch($video->id)->onQueue('video-generation');

        return $video;
    }
}

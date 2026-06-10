<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVideoRequest;
use App\Models\Video;
use App\Services\VideoCreationService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApiVideoController extends Controller
{
    public function index(): JsonResponse
    {
        $videos = Video::latest()->get()->map(fn(Video $v) => $this->transform($v));

        return response()->json(['data' => $videos]);
    }

    public function store(StoreVideoRequest $request, VideoCreationService $service): JsonResponse
    {
        $video = $service->create($request->videoAttributes(), $request);

        return response()->json(['data' => $this->transform($video)], 201);
    }

    public function show(Video $video): JsonResponse
    {
        return response()->json(['data' => $this->transform($video)]);
    }

    public function destroy(Video $video): JsonResponse
    {
        if ($video->video_path && file_exists($video->video_path)) {
            @unlink($video->video_path);
        }
        if ($video->srt_path && file_exists($video->srt_path)) {
            @unlink($video->srt_path);
        }

        $video->delete();

        return response()->json(['deleted' => true]);
    }

    public function download(Video $video): BinaryFileResponse|JsonResponse
    {
        if (!$video->isDone() || !$video->video_path || !file_exists($video->video_path)) {
            return response()->json(['error' => 'Vídeo não disponível'], 404);
        }

        return response()->download($video->video_path, "video_{$video->id}.mp4");
    }

    public function subtitles(Video $video): BinaryFileResponse|JsonResponse
    {
        if (!$video->isDone() || !$video->srt_path || !file_exists($video->srt_path)) {
            return response()->json(['error' => 'Legenda não disponível'], 404);
        }

        return response()->download($video->srt_path, "legenda_{$video->id}.srt");
    }

    private function transform(Video $video): array
    {
        return [
            'id'           => $video->id,
            'tema'         => $video->tema,
            'duracao'      => $video->duracao,
            'idioma'       => $video->idioma ?? 'PT-BR',
            'voz'          => $video->voz,
            'status'       => $video->status,
            'status_label' => $video->statusLabel(),
            'progresso'    => $video->progresso,
            'erro'         => $video->erro,
            'done'         => $video->isDone(),
            'failed'       => $video->hasFailed(),
            'processing'   => $video->isProcessing(),
            'created_at'   => $video->created_at?->toIso8601String(),
            'updated_at'   => $video->updated_at?->toIso8601String(),
            'urls'         => [
                'self'      => url("/api/videos/{$video->id}"),
                'download'  => $video->isDone() ? url("/api/videos/{$video->id}/download")  : null,
                'subtitles' => $video->isDone() ? url("/api/videos/{$video->id}/subtitles") : null,
            ],
        ];
    }
}

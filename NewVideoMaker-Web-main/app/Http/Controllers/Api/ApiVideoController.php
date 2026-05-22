<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\VoiceController;
use App\Jobs\GerarVideo;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApiVideoController extends Controller
{
    public function index(): JsonResponse
    {
        $videos = Video::latest()->get()->map(fn(Video $v) => $this->transform($v));

        return response()->json(['data' => $videos]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tema'           => ['required', 'string', 'max:200'],
            'duracao'        => ['required', 'integer', 'min:15', 'max:120'],
            'idioma'         => ['nullable', 'in:PT-BR,EN-US'],
            'voz'            => ['nullable', Rule::in(VoiceController::allVoiceIds())],
            'imagens_modo'   => ['nullable', 'in:gerar,upload'],
            'narracao_modo'  => ['nullable', 'in:gerar,upload,nenhum'],
            'musica_modo'    => ['nullable', 'in:gerar,upload,nenhum'],
            'legendas_modo'  => ['nullable', 'in:gerar,upload,nenhum'],
            'imagens'        => ['required_if:imagens_modo,upload', 'array', 'min:1', 'max:20'],
            'imagens.*'      => ['file', 'mimes:jpg,jpeg,png,webp,bmp', 'max:10240'],
            'narracao'       => ['required_if:narracao_modo,upload', 'file', 'mimes:mp3,wav,m4a,ogg,aac,flac', 'max:51200'],
            'musica'         => ['required_if:musica_modo,upload',   'file', 'mimes:mp3,wav,m4a,ogg,aac,flac', 'max:102400'],
            'legendas'       => ['required_if:legendas_modo,upload', 'file', 'mimes:srt,vtt,txt',              'max:2048'],
        ]);

        $video = Video::create([
            'tema'           => $data['tema'],
            'duracao'        => $data['duracao'],
            'idioma'         => $data['idioma'] ?? 'PT-BR',
            'voz'            => $data['voz'] ?? null,
            'imagens_modo'   => $data['imagens_modo']  ?? 'gerar',
            'narracao_modo'  => $data['narracao_modo'] ?? 'gerar',
            'musica_modo'    => $data['musica_modo']   ?? 'gerar',
            'legendas_modo'  => $data['legendas_modo'] ?? 'gerar',
        ]);

        $uploadDir = storage_path("app/uploads/{$video->id}");

        if (($data['imagens_modo'] ?? null) === 'upload') {
            $destImg = "{$uploadDir}/imagens";
            if (!is_dir($destImg)) {
                mkdir($destImg, 0755, true);
            }
            foreach ($request->file('imagens', []) as $i => $file) {
                $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
                $file->move($destImg, sprintf('cena%02d.%s', $i + 1, $ext));
            }
        }

        if (($data['narracao_modo'] ?? null) === 'upload') {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file = $request->file('narracao');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'mp3');
            $file->move($uploadDir, "narracao.{$ext}");
        }

        if (($data['musica_modo'] ?? null) === 'upload') {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file = $request->file('musica');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'mp3');
            $file->move($uploadDir, "musica.{$ext}");
        }

        if (($data['legendas_modo'] ?? null) === 'upload') {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file = $request->file('legendas');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'srt');
            $file->move($uploadDir, "legenda.{$ext}");
        }

        GerarVideo::dispatch($video->id)->onQueue('video-generation');

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

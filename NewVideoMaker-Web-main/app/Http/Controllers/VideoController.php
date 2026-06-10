<?php

namespace App\Http\Controllers;

use App\Http\Controllers\VoiceController;
use App\Http\Requests\StoreVideoRequest;
use App\Models\Video;
use App\Services\VideoCreationService;
use App\Services\VideoRegenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VideoController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $videos = Video::latest()->get();

        return view('videos.index', compact('videos'));
    }

    public function dashboard(): \Illuminate\View\View
    {
        $videos = Video::query()->latest()->get();

        $stats = [
            'total' => $videos->count(),
            'done' => $videos->where('status', 'done')->count(),
            'failed' => $videos->where('status', 'failed')->count(),
            'processing' => $videos->reject(fn (Video $video) => $video->isDone() || $video->hasFailed())->count(),
        ];

        $recentVideos = $videos->take(5);

        return view('pages.dashboard', compact('stats', 'recentVideos'));
    }

    public function create(): \Illuminate\View\View
    {
        return view('videos.create');
    }

    public function store(StoreVideoRequest $request, VideoCreationService $service): RedirectResponse
    {
        $video = $service->create($request->videoAttributes(), $request);

        return redirect()->route('videos.status', $video);
    }

    public function status(Video $video): \Illuminate\View\View
    {
        return view('videos.status', compact('video'));
    }

    public function poll(Video $video): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'      => $video->status,
            'statusLabel' => $video->statusLabel(),
            'progresso'   => $video->progresso,
            'done'        => $video->isDone(),
            'failed'      => $video->hasFailed(),
            'erro'        => $video->erro,
        ]);
    }

    public function show(Video $video): \Illuminate\View\View
    {
        abort_unless($video->isDone(), 404);

        return view('videos.show', compact('video'));
    }

    public function download(Video $video): BinaryFileResponse
    {
        abort_unless($video->isDone() && $video->video_path, 404);
        abort_unless(file_exists($video->video_path), 404);

        return response()->download($video->video_path, "video_{$video->id}.mp4");
    }

    public function downloadSrt(Video $video): BinaryFileResponse
    {
        abort_unless($video->isDone() && $video->srt_path, 404);
        abort_unless(file_exists($video->srt_path), 404);

        return response()->download($video->srt_path, "legenda_{$video->id}.srt");
    }

    public function thumbnail(Video $video): BinaryFileResponse
    {
        $path = $video->thumbnail_path;

        // Fallback para vídeos antigos: usa a primeira imagem persistida.
        if (!$path || !file_exists($path)) {
            $imagens = $video->imagens_paths ?? [];
            $path = $imagens[0] ?? null;
        }

        abort_unless($path && file_exists($path), 404);

        return response()->file($path, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function destroy(Video $video): RedirectResponse
    {
        // Não permite excluir vídeo em processamento (evita race com o worker).
        if ($video->isProcessing()) {
            return redirect()
                ->route('videos.index')
                ->with('error', __('Não é possível excluir um vídeo em processamento.'));
        }

        // Remove arquivos persistidos e diretórios de upload/artifacts.
        foreach ([$video->video_path, $video->srt_path] as $path) {
            if ($path && file_exists($path)) {
                @unlink($path);
            }
        }

        $this->removeDir(storage_path("app/uploads/{$video->id}"));
        $this->removeDir(storage_path("app/artifacts/{$video->id}"));

        $video->delete();

        return redirect()
            ->route('videos.index')
            ->with('success', __('Vídeo excluído com sucesso.'));
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($dir);
    }

    public function regenerate(Video $video, Request $request, VideoRegenerationService $service): RedirectResponse
    {
        abort_unless($video->isDone(), 404);

        $data = $request->validate([
            'keep'           => ['nullable', 'array'],
            'keep.*'         => ['in:imagens,narracao,musica'],
            'voz'            => ['nullable', Rule::in(VoiceController::allVoiceIds())],
            'idioma'         => ['nullable', 'in:PT-BR,EN-US'],
        ]);

        $overrides = array_filter([
            'voz'    => $data['voz']    ?? null,
            'idioma' => $data['idioma'] ?? null,
        ], fn ($v) => $v !== null);

        $novo = $service->regenerate($video, $data['keep'] ?? [], $overrides);

        return redirect()->route('videos.status', $novo);
    }
}

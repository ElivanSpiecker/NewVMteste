<?php

namespace App\Http\Controllers;

use App\Http\Controllers\VoiceController;
use App\Jobs\GerarVideo;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tema'           => ['required', 'string', 'max:200'],
            'duracao'        => ['required', 'integer', 'min:15', 'max:120'],
            'idioma'         => ['required', 'in:PT-BR,EN-US'],
            'voz'            => ['nullable', Rule::in(VoiceController::allVoiceIds())],
            'imagens_modo'   => ['required', 'in:gerar,upload'],
            'narracao_modo'  => ['required', 'in:gerar,upload,nenhum'],
            'musica_modo'    => ['required', 'in:gerar,upload,nenhum'],
            'legendas_modo'  => ['required', 'in:gerar,upload,nenhum'],
            'imagens'        => ['required_if:imagens_modo,upload', 'array', 'min:1', 'max:20'],
            'imagens.*'      => ['file', 'mimes:jpg,jpeg,png,webp,bmp', 'max:10240'],
            'narracao'       => ['required_if:narracao_modo,upload', 'file', 'mimes:mp3,wav,m4a,ogg,aac,flac', 'max:51200'],
            'musica'         => ['required_if:musica_modo,upload',   'file', 'mimes:mp3,wav,m4a,ogg,aac,flac', 'max:102400'],
            'legendas'       => ['required_if:legendas_modo,upload', 'file', 'mimes:srt,vtt,txt',              'max:2048'],
        ]);

        $video = Video::create([
            'tema'           => $data['tema'],
            'duracao'        => $data['duracao'],
            'idioma'         => $data['idioma'],
            'voz'            => $data['voz'] ?? null,
            'imagens_modo'   => $data['imagens_modo'],
            'narracao_modo'  => $data['narracao_modo'],
            'musica_modo'    => $data['musica_modo'],
            'legendas_modo'  => $data['legendas_modo'],
        ]);

        // Salva uploads em storage/app/uploads/{id}/ — paths absolutos são passados ao pipeline pelo Job
        $uploadDir = storage_path("app/uploads/{$video->id}");

        if ($data['imagens_modo'] === 'upload') {
            $destImg = "{$uploadDir}/imagens";
            if (!is_dir($destImg)) {
                mkdir($destImg, 0755, true);
            }
            foreach ($request->file('imagens', []) as $i => $file) {
                $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
                $file->move($destImg, sprintf('cena%02d.%s', $i + 1, $ext));
            }
        }

        if ($data['narracao_modo'] === 'upload') {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file = $request->file('narracao');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'mp3');
            $file->move($uploadDir, "narracao.{$ext}");
        }

        if ($data['musica_modo'] === 'upload') {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file = $request->file('musica');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'mp3');
            $file->move($uploadDir, "musica.{$ext}");
        }

        if ($data['legendas_modo'] === 'upload') {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file = $request->file('legendas');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'srt');
            $file->move($uploadDir, "legenda.{$ext}");
        }

        GerarVideo::dispatch($video->id)->onQueue('video-generation');

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
}

<?php

namespace App\Http\Controllers;

use App\Jobs\GerarVideo;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'tema'    => ['required', 'string', 'max:200'],
            'duracao' => ['required', 'integer', 'min:15', 'max:120'],
        ]);

        $video = Video::create($data);

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

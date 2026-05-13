<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\YoutubePost;
use App\Services\YoutubeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class YoutubePostController extends Controller
{
    public function index(YoutubeService $youtubeService): View
    {
        $posts = YoutubePost::with('video')->latest()->get();
        $connected = $youtubeService->hasToken();

        return view('youtube.index', compact('posts', 'connected'));
    }

    public function create(Video $video, YoutubeService $youtubeService): View
    {
        abort_unless($video->isDone(), 404);

        $connected = $youtubeService->hasToken();

        return view('youtube.create', compact('video', 'connected'));
    }

    public function store(Request $request, Video $video): RedirectResponse
    {
        abort_unless($video->isDone(), 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'tags' => ['nullable', 'string', 'max:500'],
            'category_id' => ['required', 'string', 'max:10'],
            'privacy_status' => ['required', 'in:private,unlisted,public'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $data['video_id'] = $video->id;
        $data['status'] = 'scheduled';

        YoutubePost::create($data);

        return redirect()->route('youtube.index')->with('success', 'Postagem do YouTube agendada.');
    }

    public function connect(YoutubeService $youtubeService): RedirectResponse
    {
        return redirect()->away($youtubeService->authUrl());
    }

    public function callback(Request $request, YoutubeService $youtubeService): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('youtube.index')->with('error', 'Permissão negada: '.$request->string('error'));
        }

        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $youtubeService->saveTokenFromCode($request->string('code'));

        return redirect()->route('youtube.index')->with('success', 'Conta do YouTube conectada com sucesso.');
    }

    public function publishNow(YoutubePost $post, YoutubeService $youtubeService): RedirectResponse
    {
        if (!in_array($post->status, ['scheduled', 'failed'], true)) {
            return redirect()->route('youtube.index')->with('error', 'Essa postagem não pode ser publicada agora.');
        }

        try {
            $post->update(['status' => 'publishing', 'error' => null]);
            $youtubeId = $youtubeService->upload($post);
            $post->update([
                'status' => 'published',
                'youtube_video_id' => $youtubeId,
                'published_at' => now(),
                'error' => null,
            ]);

            return redirect()->route('youtube.index')->with('success', 'Vídeo enviado para o YouTube.');
        } catch (\Throwable $exception) {
            $post->update([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('youtube.index')->with('error', 'Falha ao publicar: '.$exception->getMessage());
        }
    }
}

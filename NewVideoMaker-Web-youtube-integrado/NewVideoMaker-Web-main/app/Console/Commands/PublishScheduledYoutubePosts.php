<?php

namespace App\Console\Commands;

use App\Models\YoutubePost;
use App\Services\YoutubeService;
use Illuminate\Console\Command;

class PublishScheduledYoutubePosts extends Command
{
    protected $signature = 'youtube:publish-scheduled';

    protected $description = 'Publica no YouTube os vídeos agendados cujo horário já chegou.';

    public function handle(YoutubeService $youtubeService): int
    {
        $posts = YoutubePost::with('video')
            ->where('status', 'scheduled')
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->orderBy('scheduled_at')
            ->limit(3)
            ->get();

        if ($posts->isEmpty()) {
            $this->info('Nenhuma postagem pendente.');
            return self::SUCCESS;
        }

        foreach ($posts as $post) {
            try {
                $this->info("Publicando postagem #{$post->id}...");
                $post->update(['status' => 'publishing', 'error' => null]);

                $youtubeId = $youtubeService->upload($post);

                $post->update([
                    'status' => 'published',
                    'youtube_video_id' => $youtubeId,
                    'published_at' => now(),
                    'error' => null,
                ]);

                $this->info("Publicado: {$youtubeId}");
            } catch (\Throwable $exception) {
                $post->update([
                    'status' => 'failed',
                    'error' => $exception->getMessage(),
                ]);

                $this->error("Falha na postagem #{$post->id}: {$exception->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}

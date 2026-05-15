<?php

namespace App\Jobs;

use App\Models\YoutubeUpload;
use App\Services\YoutubeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class PublicarYoutubeShort implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 1500; // 25 min — uploads grandes
    public int $tries   = 1;

    public function __construct(public readonly int $uploadId)
    {
    }

    public function handle(YoutubeService $youtube): void
    {
        $upload = YoutubeUpload::with('account')->findOrFail($this->uploadId);

        if ($upload->status === 'published') {
            return; // idempotência
        }

        $upload->update(['status' => 'uploading', 'progresso' => 5, 'erro' => null]);

        try {
            $publishAt = null;
            if ($upload->scheduled_at && $upload->scheduled_at->isFuture()) {
                // YouTube exige RFC 3339 / ISO 8601 com offset; convertemos para UTC explicitamente
                $publishAt = $upload->scheduled_at->copy()->utc()->format('Y-m-d\TH:i:s\Z');
            }

            $tags = $upload->tagsArray();
            // YouTube limita o tamanho total das tags em 500 caracteres (com vírgulas)
            $tagsJoined = implode(',', $tags);
            if (mb_strlen($tagsJoined) > 500) {
                $tags = $this->truncateTags($tags, 500);
            }

            $upload->update(['progresso' => 15]);

            $result = $youtube->uploadVideo($upload->account, $upload->source_path, [
                'title'         => $upload->title,
                'description'   => (string) $upload->description,
                'tags'          => $tags,
                'categoryId'    => (string) $upload->category_id,
                'privacyStatus' => $upload->privacy_status,
                'publishAt'     => $publishAt,
                'madeForKids'   => (bool) $upload->made_for_kids,
            ]);

            $upload->update([
                'status'           => $publishAt ? 'scheduled' : 'published',
                'progresso'        => 100,
                'youtube_video_id' => $result['id'],
                'erro'             => null,
            ]);
        } catch (Throwable $e) {
            $upload->update([
                'status'    => 'failed',
                'progresso' => 0,
                'erro'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        YoutubeUpload::where('id', $this->uploadId)->update([
            'status' => 'failed',
            'erro'   => $exception->getMessage(),
        ]);
    }

    /**
     * Reduz a lista de tags até caber no limite (em caracteres, separadas por vírgula).
     */
    private function truncateTags(array $tags, int $maxChars): array
    {
        $out = [];
        $len = 0;
        foreach ($tags as $tag) {
            $extra = ($len === 0 ? 0 : 1) + mb_strlen($tag);
            if ($len + $extra > $maxChars) {
                break;
            }
            $out[] = $tag;
            $len  += $extra;
        }
        return $out;
    }
}

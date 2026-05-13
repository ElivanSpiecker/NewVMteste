<?php

namespace App\Services;

use App\Models\YoutubePost;
use Carbon\CarbonInterface;
use Google\Client;
use Google\Http\MediaFileUpload;
use Google\Service\YouTube;
use Google\Service\YouTube\Video as YoutubeVideo;
use Google\Service\YouTube\VideoSnippet;
use Google\Service\YouTube\VideoStatus;
use Illuminate\Support\Carbon;
use RuntimeException;

class YoutubeService
{
    private string $tokenPath;

    public function __construct()
    {
        $this->tokenPath = storage_path('app/youtube/token.json');
    }

    public function makeClient(): Client
    {
        $client = new Client();
        $client->setClientId(config('services.youtube.client_id'));
        $client->setClientSecret(config('services.youtube.client_secret'));
        $client->setRedirectUri(route('youtube.callback'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([YouTube::YOUTUBE_UPLOAD]);

        return $client;
    }

    public function authUrl(): string
    {
        return $this->makeClient()->createAuthUrl();
    }

    public function saveTokenFromCode(string $code): void
    {
        $client = $this->makeClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new RuntimeException($token['error_description'] ?? $token['error']);
        }

        if (!is_dir(dirname($this->tokenPath))) {
            mkdir(dirname($this->tokenPath), 0755, true);
        }

        file_put_contents($this->tokenPath, json_encode($token, JSON_PRETTY_PRINT));
    }

    public function hasToken(): bool
    {
        return file_exists($this->tokenPath);
    }

    public function authenticatedClient(): Client
    {
        if (!$this->hasToken()) {
            throw new RuntimeException('Conta do YouTube ainda não conectada. Acesse a tela do YouTube e clique em Conectar conta.');
        }

        $client = $this->makeClient();
        $token = json_decode(file_get_contents($this->tokenPath), true);
        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {
            $refreshToken = $client->getRefreshToken() ?: ($token['refresh_token'] ?? null);

            if (!$refreshToken) {
                throw new RuntimeException('Token expirado e sem refresh_token. Conecte a conta do YouTube novamente.');
            }

            $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            $newToken['refresh_token'] = $refreshToken;
            file_put_contents($this->tokenPath, json_encode($newToken, JSON_PRETTY_PRINT));
            $client->setAccessToken($newToken);
        }

        return $client;
    }

    public function upload(YoutubePost $post): string
    {
        $post->loadMissing('video');

        $path = $post->video->video_path;
        if (!$path || !file_exists($path)) {
            throw new RuntimeException('Arquivo MP4 não encontrado para upload.');
        }

        $client = $this->authenticatedClient();
        $youtube = new YouTube($client);

        $snippet = new VideoSnippet();
        $snippet->setTitle($post->title);
        $snippet->setDescription($post->description ?: '');
        $snippet->setCategoryId($post->category_id ?: '22');

        $tags = collect(explode(',', (string) $post->tags))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->values()
            ->all();

        if ($tags) {
            $snippet->setTags($tags);
        }

        $status = new VideoStatus();
        $status->setPrivacyStatus($post->privacy_status ?: 'private');
        $status->setSelfDeclaredMadeForKids(false);

        if ($post->scheduled_at && $post->scheduled_at->isFuture()) {
            $status->setPrivacyStatus('private');
            $status->setPublishAt($this->toYoutubeDateTime($post->scheduled_at));
        }

        $video = new YoutubeVideo();
        $video->setSnippet($snippet);
        $video->setStatus($status);

        $client->setDefer(true);
        $insertRequest = $youtube->videos->insert('status,snippet', $video);

        $chunkSizeBytes = 2 * 1024 * 1024;
        $media = new MediaFileUpload(
            $client,
            $insertRequest,
            'video/*',
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($path));

        $handle = fopen($path, 'rb');
        $uploadStatus = false;

        while (!$uploadStatus && !feof($handle)) {
            $chunk = fread($handle, $chunkSizeBytes);
            $uploadStatus = $media->nextChunk($chunk);
        }

        fclose($handle);
        $client->setDefer(false);

        if (!$uploadStatus || !$uploadStatus->getId()) {
            throw new RuntimeException('Upload enviado, mas o YouTube não retornou o ID do vídeo.');
        }

        return $uploadStatus->getId();
    }

    private function toYoutubeDateTime(CarbonInterface $date): string
    {
        return Carbon::parse($date)->utc()->toRfc3339String();
    }
}

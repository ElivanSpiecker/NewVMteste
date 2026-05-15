<?php

namespace App\Services;

use App\Models\YoutubeAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Wrapper minimalista da YouTube Data API v3 + OAuth 2.0.
 *
 * Não dependemos do SDK oficial do Google — usamos a HTTP API direta
 * para evitar trazer dezenas de pacotes Composer. Inclui:
 *   - troca de "code" do OAuth por tokens (authorization code flow)
 *   - refresh do access_token quando expirado
 *   - upload resumível (resumable upload) com publishAt opcional
 *   - leitura básica do canal autenticado (channels.list mine=true)
 */
class YoutubeService
{
    private const OAUTH_AUTHORIZE_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const OAUTH_TOKEN_URL     = 'https://oauth2.googleapis.com/token';
    private const OAUTH_REVOKE_URL    = 'https://oauth2.googleapis.com/revoke';

    private const API_CHANNELS = 'https://www.googleapis.com/youtube/v3/channels';
    private const UPLOAD_VIDEOS = 'https://www.googleapis.com/upload/youtube/v3/videos';

    public const DEFAULT_SCOPES = [
        'https://www.googleapis.com/auth/youtube.upload',
        'https://www.googleapis.com/auth/youtube.readonly',
    ];

    public function clientId(): string
    {
        $id = (string) config('services.youtube.client_id');
        if ($id === '') {
            throw new RuntimeException('YOUTUBE_CLIENT_ID não configurado em .env / config/services.php.');
        }
        return $id;
    }

    public function clientSecret(): string
    {
        $secret = (string) config('services.youtube.client_secret');
        if ($secret === '') {
            throw new RuntimeException('YOUTUBE_CLIENT_SECRET não configurado em .env / config/services.php.');
        }
        return $secret;
    }

    public function redirectUri(): string
    {
        $uri = (string) config('services.youtube.redirect');
        if ($uri === '') {
            throw new RuntimeException('YOUTUBE_REDIRECT_URI não configurado em .env / config/services.php.');
        }
        return $uri;
    }

    /**
     * URL para iniciar o consentimento OAuth.
     */
    public function buildAuthorizationUrl(string $state, array $scopes = self::DEFAULT_SCOPES): string
    {
        $params = http_build_query([
            'client_id'              => $this->clientId(),
            'redirect_uri'           => $this->redirectUri(),
            'response_type'          => 'code',
            'scope'                  => implode(' ', $scopes),
            'access_type'            => 'offline', // exige refresh_token
            'prompt'                 => 'consent', // garante refresh_token mesmo se já autorizou antes
            'include_granted_scopes' => 'true',
            'state'                  => $state,
        ]);

        return self::OAUTH_AUTHORIZE_URL.'?'.$params;
    }

    /**
     * Troca o "code" do callback por access_token + refresh_token.
     * Retorna array já normalizado (token, refresh, expires_at, scope).
     */
    public function exchangeCodeForTokens(string $code): array
    {
        $response = Http::asForm()->post(self::OAUTH_TOKEN_URL, [
            'code'          => $code,
            'client_id'     => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'redirect_uri'  => $this->redirectUri(),
            'grant_type'    => 'authorization_code',
        ]);

        if ($response->failed()) {
            Log::warning('YouTube OAuth: falha na troca do código', ['body' => $response->body()]);
            throw new RuntimeException('Não foi possível obter o token do YouTube: '.$response->body());
        }

        $payload = $response->json();

        return [
            'access_token'  => $payload['access_token'] ?? null,
            'refresh_token' => $payload['refresh_token'] ?? null,
            'expires_at'    => isset($payload['expires_in']) ? Carbon::now()->addSeconds((int) $payload['expires_in'] - 30) : null,
            'scope'         => $payload['scope'] ?? null,
        ];
    }

    /**
     * Garante que a conta possui access_token válido. Faz refresh se necessário.
     */
    public function ensureValidAccessToken(YoutubeAccount $account): string
    {
        if (!$account->expiresSoon(60) && !$account->isExpired()) {
            return $account->access_token;
        }

        if (empty($account->refresh_token)) {
            throw new RuntimeException('Conta sem refresh_token: reconecte o YouTube.');
        }

        $response = Http::asForm()->post(self::OAUTH_TOKEN_URL, [
            'client_id'     => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'refresh_token' => $account->refresh_token,
            'grant_type'    => 'refresh_token',
        ]);

        if ($response->failed()) {
            Log::warning('YouTube OAuth: falha ao renovar token', ['body' => $response->body()]);
            throw new RuntimeException('Falha ao renovar token do YouTube: '.$response->body());
        }

        $payload = $response->json();

        $account->update([
            'access_token' => $payload['access_token'] ?? $account->access_token,
            'expires_at'   => isset($payload['expires_in'])
                ? Carbon::now()->addSeconds((int) $payload['expires_in'] - 30)
                : null,
        ]);

        return $account->fresh()->access_token;
    }

    /**
     * Busca dados do canal autenticado (mine=true) para preencher channel_id/title.
     */
    public function fetchOwnChannel(string $accessToken): ?array
    {
        $response = Http::withToken($accessToken)
            ->get(self::API_CHANNELS, [
                'part' => 'snippet',
                'mine' => 'true',
            ]);

        if ($response->failed()) {
            Log::warning('YouTube channels.list falhou', ['body' => $response->body()]);
            return null;
        }

        $items = $response->json('items') ?? [];
        if (empty($items)) {
            return null;
        }

        $first = $items[0];
        return [
            'id'    => $first['id'] ?? null,
            'title' => $first['snippet']['title'] ?? null,
        ];
    }

    /**
     * Revoga o token (best-effort).
     */
    public function revoke(string $token): void
    {
        try {
            Http::asForm()->post(self::OAUTH_REVOKE_URL, ['token' => $token]);
        } catch (\Throwable $e) {
            Log::info('Revoke do YouTube falhou (ignorado)', ['err' => $e->getMessage()]);
        }
    }

    /**
     * Faz upload resumível de um arquivo de vídeo para o canal autenticado.
     *
     * @param array{title:string,description?:string,tags?:array<int,string>,categoryId?:string,privacyStatus?:string,publishAt?:?string,madeForKids?:bool} $snippetAndStatus
     * @return array{id:string,raw:array}
     */
    public function uploadVideo(YoutubeAccount $account, string $filePath, array $snippetAndStatus): array
    {
        if (!is_file($filePath)) {
            throw new RuntimeException("Arquivo de vídeo não encontrado: {$filePath}");
        }

        $accessToken = $this->ensureValidAccessToken($account);

        $title       = mb_substr((string) ($snippetAndStatus['title'] ?? 'Sem título'), 0, 100);
        $description = (string) ($snippetAndStatus['description'] ?? '');
        $tags        = $snippetAndStatus['tags'] ?? [];
        $categoryId  = (string) ($snippetAndStatus['categoryId'] ?? '22');
        $privacy     = (string) ($snippetAndStatus['privacyStatus'] ?? 'public');
        $publishAt   = $snippetAndStatus['publishAt'] ?? null; // ISO 8601 UTC ou null
        $madeForKids = (bool) ($snippetAndStatus['madeForKids'] ?? false);

        $status = [
            'privacyStatus'      => $publishAt ? 'private' : $privacy, // YouTube exige privacyStatus=private quando publishAt é informado
            'selfDeclaredMadeForKids' => $madeForKids,
        ];
        if ($publishAt) {
            $status['publishAt'] = $publishAt;
        }

        $metadata = [
            'snippet' => [
                'title'       => $title,
                'description' => $description,
                'tags'        => array_values(array_filter($tags)),
                'categoryId'  => $categoryId,
            ],
            'status' => $status,
        ];

        $fileSize = filesize($filePath) ?: 0;
        $mimeType = $this->detectMimeType($filePath);

        // Step 1 — inicia sessão resumível, recebendo a "Location" para subir o binário.
        $initResponse = Http::withToken($accessToken)
            ->withHeaders([
                'X-Upload-Content-Length' => (string) $fileSize,
                'X-Upload-Content-Type'   => $mimeType,
                'Content-Type'            => 'application/json; charset=UTF-8',
            ])
            ->withBody(json_encode($metadata, JSON_UNESCAPED_UNICODE), 'application/json')
            ->post(self::UPLOAD_VIDEOS.'?uploadType=resumable&part=snippet,status');

        if ($initResponse->failed()) {
            throw new RuntimeException('Falha ao iniciar upload no YouTube: '.$initResponse->body());
        }

        $uploadUrl = $initResponse->header('Location');
        if (empty($uploadUrl)) {
            throw new RuntimeException('YouTube não retornou Location para upload resumível.');
        }

        // Step 2 — envia o binário em uma única requisição PUT (sem chunking, vídeos curtos).
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            throw new RuntimeException("Não foi possível abrir o arquivo de vídeo: {$filePath}");
        }

        try {
            $uploadResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type'   => $mimeType,
                    'Content-Length' => (string) $fileSize,
                ])
                ->withBody(stream_get_contents($handle), $mimeType)
                ->timeout(900) // até 15 min para o upload
                ->put($uploadUrl);
        } finally {
            fclose($handle);
        }

        if ($uploadResponse->failed()) {
            throw new RuntimeException('Falha no envio do vídeo ao YouTube: '.$uploadResponse->body());
        }

        $payload = $uploadResponse->json();
        $videoId = $payload['id'] ?? null;
        if (!$videoId) {
            throw new RuntimeException('YouTube não retornou o ID do vídeo após upload.');
        }

        return [
            'id'  => $videoId,
            'raw' => $payload,
        ];
    }

    private function detectMimeType(string $filePath): string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return match ($ext) {
            'mp4', 'm4v' => 'video/mp4',
            'mov'        => 'video/quicktime',
            'avi'        => 'video/x-msvideo',
            'wmv'        => 'video/x-ms-wmv',
            'mpeg', 'mpg' => 'video/mpeg',
            'webm'       => 'video/webm',
            'mkv'        => 'video/x-matroska',
            'flv'        => 'video/x-flv',
            '3gp'        => 'video/3gpp',
            default      => 'application/octet-stream',
        };
    }
}

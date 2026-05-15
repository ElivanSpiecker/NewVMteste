<?php

namespace App\Http\Controllers;

use App\Models\YoutubeAccount;
use App\Services\YoutubeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class YoutubeAuthController extends Controller
{
    public function __construct(private readonly YoutubeService $youtube)
    {
    }

    public function redirect(Request $request): RedirectResponse
    {
        try {
            $state = Str::random(40);
            $request->session()->put('youtube_oauth_state', $state);

            return redirect()->away($this->youtube->buildAuthorizationUrl($state));
        } catch (Throwable $e) {
            return redirect()->route('shorts.index')->with('error', $e->getMessage());
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route('shorts.index')->with('error', 'Autorização cancelada: '.$request->string('error'));
        }

        $expectedState = $request->session()->pull('youtube_oauth_state');
        $receivedState = $request->string('state')->toString();

        if (empty($expectedState) || $expectedState !== $receivedState) {
            return redirect()->route('shorts.index')->with('error', 'State OAuth inválido. Tente conectar novamente.');
        }

        $code = $request->string('code')->toString();
        if ($code === '') {
            return redirect()->route('shorts.index')->with('error', 'Nenhum código recebido do Google.');
        }

        try {
            $tokens = $this->youtube->exchangeCodeForTokens($code);
            if (empty($tokens['access_token'])) {
                throw new \RuntimeException('Resposta do Google não trouxe access_token.');
            }

            $channel = $this->youtube->fetchOwnChannel($tokens['access_token']);

            $account = YoutubeAccount::updateOrCreate(
                ['channel_id' => $channel['id'] ?? null],
                [
                    'channel_title' => $channel['title'] ?? null,
                    'display_name'  => $channel['title'] ?? 'Conta YouTube',
                    'access_token'  => $tokens['access_token'],
                    // Se Google não devolveu refresh_token nesse fluxo (já existia consentimento),
                    // mantemos o refresh_token salvo anteriormente.
                    'refresh_token' => $tokens['refresh_token'] ?? optional(YoutubeAccount::where('channel_id', $channel['id'] ?? null)->first())->refresh_token,
                    'expires_at'    => $tokens['expires_at'] ?? null,
                    'scopes'        => $tokens['scope'] ?? null,
                ]
            );

            return redirect()->route('shorts.index')->with('success', "Canal \"{$account->display_name}\" conectado com sucesso.");
        } catch (Throwable $e) {
            return redirect()->route('shorts.index')->with('error', 'Falha ao conectar com o YouTube: '.$e->getMessage());
        }
    }

    public function disconnect(YoutubeAccount $account): RedirectResponse
    {
        try {
            if (!empty($account->refresh_token)) {
                $this->youtube->revoke($account->refresh_token);
            }
            $account->delete();
            return redirect()->route('shorts.index')->with('success', 'Conta desconectada.');
        } catch (Throwable $e) {
            return redirect()->route('shorts.index')->with('error', 'Não foi possível desconectar: '.$e->getMessage());
        }
    }
}

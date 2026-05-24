<?php

namespace App\Http\Middleware;

use App\Services\AppConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redireciona pra /setup na primeira execucao.
 * Cliente que clicou em "Pular" ou "Concluir" nao e mais incomodado.
 *
 * Nao chama o ServiceDetector aqui pra evitar overhead em toda request --
 * usa apenas AppConfig::pendencies() que e barato (DB + cache).
 */
class RedirectSeSetupIncompleto
{
    public function __construct(private readonly AppConfig $appConfig)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Nao redireciona em rotas do proprio wizard, config, health, downloads, assets, OAuth callback.
        $rota = (string) $request->route()?->getName();
        $excluidas = [
            'setup.', 'config', 'config.', 'health.',
            'videos.download', 'videos.download-srt',
            'shorts.callback', 'shorts.connect',
        ];
        foreach ($excluidas as $prefixo) {
            if ($rota === rtrim($prefixo, '.') || str_starts_with($rota, $prefixo)) {
                return $next($request);
            }
        }

        // Se cliente ja concluiu OU pulou, nao incomoda mais
        if ($this->appConfig->get('setup.completed') === '1' || $this->appConfig->get('setup.skipped') === '1') {
            return $next($request);
        }

        // Source of truth unica: AppConfig::pendencies() lista tudo que precisa configurar.
        // Se nao tem pendencia, marca completo e segue. Se tem, manda pro wizard.
        if (empty($this->appConfig->pendencies())) {
            $this->appConfig->set('setup.completed', '1');
            return $next($request);
        }

        if ($request->isMethod('GET') && !$request->expectsJson()) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}

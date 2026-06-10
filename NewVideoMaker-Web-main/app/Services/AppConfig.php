<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Camada única de configuração da aplicação.
 *
 * Lê primeiro o que o cliente configurou via UI (tabela app_settings),
 * com fallback para o .env quando o valor não foi salvo. Isso permite que
 * o produto seja distribuído para clientes finais sem que eles precisem
 * editar arquivos no disco.
 *
 * O método `set` invalida o cache e marca o valor como secreto se o
 * caller pedir (tokens, client_secret, etc. ficam encriptados em repouso).
 */
class AppConfig
{
    private const CACHE_KEY = 'app_settings:all';
    private const CACHE_TTL = 600; // 10 minutos

    /**
     * Mapeamento de chaves "amigáveis" → fallback no .env / config().
     */
    private const ENV_FALLBACK = [
        'youtube.client_id'     => ['env' => 'YOUTUBE_CLIENT_ID',     'config' => 'services.youtube.client_id'],
        'youtube.client_secret' => ['env' => 'YOUTUBE_CLIENT_SECRET', 'config' => 'services.youtube.client_secret'],
        'youtube.redirect_uri'  => ['env' => 'YOUTUBE_REDIRECT_URI',  'config' => 'services.youtube.redirect'],
        'videogen.python_path'  => ['env' => 'VIDEOGEN_PYTHON',       'config' => 'videogen.python_path'],
        'videogen.pipeline_path'=> ['env' => 'VIDEOGEN_PIPELINE',     'config' => 'videogen.pipeline_path'],
        'videogen.output_dir'   => ['env' => 'VIDEOGEN_OUTPUT_DIR',   'config' => 'videogen.output_dir'],
    ];

    /**
     * Chaves que sempre são tratadas como secretas (encriptadas em repouso).
     */
    private const SECRET_KEYS = [
        'youtube.client_secret',
    ];

    public function get(string $key, ?string $default = null): ?string
    {
        $stored = $this->all()[$key] ?? null;
        if ($stored !== null && $stored !== '') {
            return $stored;
        }

        $fallback = self::ENV_FALLBACK[$key] ?? null;
        if ($fallback !== null) {
            $configValue = config($fallback['config']);
            if (!empty($configValue)) {
                return (string) $configValue;
            }
        }

        return $default;
    }

    public function set(string $key, ?string $value): void
    {
        $isSecret = in_array($key, self::SECRET_KEYS, true);

        $existing = AppSetting::where('key', $key)->first();
        if ($existing === null) {
            $existing = new AppSetting(['key' => $key, 'is_secret' => $isSecret]);
        } else {
            $existing->is_secret = $isSecret;
        }
        $existing->value = $value;
        $existing->save();

        $this->flush();
    }

    /**
     * Salva múltiplos valores de uma vez. Ignora valores null para não
     * sobrescrever segredos existentes quando o usuário deixou o campo em branco.
     */
    public function setMany(array $values, bool $skipNull = true): void
    {
        foreach ($values as $key => $value) {
            if ($skipNull && ($value === null || $value === '')) {
                continue;
            }
            $this->set($key, $value);
        }
    }

    public function forget(string $key): void
    {
        AppSetting::where('key', $key)->delete();
        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Indica se uma configuração foi explicitamente salva no DB
     * (útil para diferenciar "veio do .env" de "cliente configurou").
     */
    public function isSet(string $key): bool
    {
        return array_key_exists($key, $this->all()) && $this->all()[$key] !== null && $this->all()[$key] !== '';
    }

    /**
     * Lista de configurações faltando para o app rodar com o pipeline + YouTube.
     * Source-of-truth única para banner global, middleware do wizard e tela /config.
     *
     * Valida não só "string preenchida" mas que o caminho realmente existe no disco --
     * cliente pode ter salvo um path que depois foi movido/excluído.
     */
    public function pendencies(): array
    {
        $out = [];

        $python = $this->get('videogen.python_path');
        if (empty($python) || !is_file($python)) {
            $out[] = 'Python do pipeline';
        }

        $pipeline = $this->get('videogen.pipeline_path');
        if (empty($pipeline) || !is_file($pipeline)) {
            $out[] = 'pipeline.py';
        }

        $output = $this->get('videogen.output_dir');
        if (empty($output) || !is_dir($output)) {
            $out[] = 'pasta de saída';
        }

        if (empty($this->get('youtube.client_id')) || empty($this->get('youtube.client_secret'))) {
            $out[] = 'credenciais do YouTube';
        }

        return $out;
    }

    /**
     * Carrega todas as settings do DB (com cache curto).
     *
     * @return array<string,?string>
     */
    private function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function (): array {
            $out = [];
            foreach (AppSetting::all() as $row) {
                $out[$row->key] = $row->value; // accessor decifra automaticamente
            }
            return $out;
        });
    }
}

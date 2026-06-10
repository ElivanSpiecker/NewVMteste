<?php

namespace App\Console\Commands;

use App\Services\AppConfig;
use App\Services\ServiceDetector;
use Illuminate\Console\Command;

/**
 * Auto-configura o app a partir de uma pasta de componentes pré-montados.
 *
 * Usado pelo postinstall.ps1 do instalador offline: depois de copiar
 * {InstallDir}\components\ (ComfyUI, ACE-Step, NewVideoMaker, ollama),
 * este comando escaneia a pasta, grava os caminhos do pipeline em
 * app_settings e reescreve launcher\launcher.config.json com os caminhos
 * absolutos de ComfyUI e ACE-Step — deixando o app 100% pronto sem o
 * cliente tocar em nada.
 *
 * Exemplo:
 *   php artisan setup:autoconfigure --components-dir="C:\NewVideoMaker\components"
 */
class SetupAutoconfigure extends Command
{
    protected $signature = 'setup:autoconfigure
        {--components-dir= : Pasta dos componentes; padrão = base_path(../components)}
        {--quiet-summary : Só imprime o resumo final}';

    protected $description = 'Detecta componentes pré-montados e configura o app automaticamente';

    public function handle(ServiceDetector $detector, AppConfig $appConfig): int
    {
        $componentsDir = $this->option('components-dir')
            ?: $this->defaultComponentsDir();

        $this->line("Componentes em: {$componentsDir}");

        if (!is_dir($componentsDir)) {
            $this->warn("Pasta de componentes não encontrada — nada a autoconfigurar.");
            $this->warn("O cliente poderá configurar manualmente em /setup.");
            return self::SUCCESS; // não é erro fatal: app web funciona, só não tem pipeline
        }

        $scan = $detector->scanComponentsDir($componentsDir);
        $configurados = [];

        // --- Pipeline NewVideoMaker (pipeline.py + python + output) ---
        if ($scan['pipeline']['found']) {
            $appConfig->set('videogen.pipeline_path', $scan['pipeline']['script']);
            $configurados[] = 'pipeline.py';

            if (!empty($scan['pipeline']['python'])) {
                $appConfig->set('videogen.python_path', $scan['pipeline']['python']);
                $configurados[] = 'Python do pipeline';
            }

            if (!empty($scan['pipeline']['output'])) {
                // Garante a pasta de saída
                if (!is_dir($scan['pipeline']['output'])) {
                    @mkdir($scan['pipeline']['output'], 0755, true);
                }
                $appConfig->set('videogen.output_dir', $scan['pipeline']['output']);
                $configurados[] = 'pasta de saída';
            }
        } else {
            $this->warn('pipeline.py não encontrado em components\\NewVideoMaker.');
        }

        // --- ComfyUI / ACE-Step: reescreve launcher.config.json ---
        $launcherUpdated = $this->updateLauncherConfig($scan, $configurados);

        // --- Marca a origem para o wizard saber que veio de payload ---
        $appConfig->set('setup.source', 'installer-payload');

        // Se pipeline + (comfyui ou acestep) ok, considera setup pronto
        if ($scan['pipeline']['found']) {
            $appConfig->set('setup.completed', '1');
            $configurados[] = 'setup marcado como concluído';
        }

        $appConfig->flush();

        // --- Resumo ---
        $this->newLine();
        $this->info('=== Autoconfigure ===');
        $this->line('  ComfyUI : '.($scan['comfyui']['found'] ? $scan['comfyui']['path'] : 'NÃO ENCONTRADO'));
        $this->line('  ACE-Step: '.($scan['acestep']['found'] ? $scan['acestep']['path'] : 'NÃO ENCONTRADO'));
        $this->line('  Pipeline: '.($scan['pipeline']['found'] ? $scan['pipeline']['script'] : 'NÃO ENCONTRADO'));
        $this->line('  launcher.config.json: '.($launcherUpdated ? 'atualizado' : 'inalterado'));
        if (!empty($configurados)) {
            $this->info('  Configurado: '.implode(', ', $configurados));
        }

        return self::SUCCESS;
    }

    /**
     * Reescreve launcher/launcher.config.json com os caminhos de ComfyUI/ACE-Step.
     */
    private function updateLauncherConfig(array $scan, array &$configurados): bool
    {
        $cfgPath = base_path('launcher/launcher.config.json');
        if (!is_file($cfgPath)) {
            $this->warn("launcher.config.json não encontrado em {$cfgPath}");
            return false;
        }

        $cfg = json_decode((string) file_get_contents($cfgPath), true);
        if (!is_array($cfg) || !isset($cfg['services'])) {
            $this->warn('launcher.config.json com formato inesperado.');
            return false;
        }

        foreach ($cfg['services'] as &$svc) {
            $name = $svc['name'] ?? '';

            if ($name === 'comfyui' && $scan['comfyui']['found']) {
                $svc['enabled']    = true;
                $svc['executable'] = $scan['comfyui']['python'] ?: 'python';
                $svc['args']       = ['-s', 'main.py', '--port', '8188'];
                $svc['workdir']    = $scan['comfyui']['path'];
                $configurados[]    = 'ComfyUI no launcher';
            }

            if ($name === 'acestep' && $scan['acestep']['found']) {
                $svc['enabled']    = true;
                $svc['executable'] = $scan['acestep']['python'] ?: 'python';
                $svc['args']       = ['app.py'];
                $svc['workdir']    = $scan['acestep']['path'];
                $configurados[]    = 'ACE-Step no launcher';
            }
        }
        unset($svc);

        file_put_contents(
            $cfgPath,
            json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        return true;
    }

    /**
     * No app instalado: {InstallDir}\app  e  {InstallDir}\components são irmãos.
     */
    private function defaultComponentsDir(): string
    {
        return dirname(base_path()).DIRECTORY_SEPARATOR.'components';
    }
}

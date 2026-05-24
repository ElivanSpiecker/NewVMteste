<?php

namespace App\Services;

/**
 * Escaneia o sistema do cliente buscando: Ollama, ComfyUI, ACE-Step,
 * FFmpeg e Python instalados. Roda em ~200ms.
 *
 * Usado pelo SetupController para mostrar o que ja tem vs o que falta
 * no wizard /setup, e para preencher automaticamente os campos de path
 * em /config (se o cliente nao informou nada manualmente).
 *
 * O detector tenta varios locais comuns por SO; quando acha, retorna o
 * path absoluto e versao (quando possivel).
 */
class ServiceDetector
{
    /**
     * Detecta Ollama. Procura binario em PATH e em %LocalAppData%\Programs\Ollama.
     */
    public function ollama(): array
    {
        $exe = $this->findExecutable('ollama.exe', [
            $this->expandEnv('%LocalAppData%\\Programs\\Ollama\\ollama.exe'),
            $this->expandEnv('%ProgramFiles%\\Ollama\\ollama.exe'),
        ]);

        if (!$exe) {
            return ['installed' => false, 'path' => null, 'running' => false, 'models' => []];
        }

        $running = $this->isPortOpen('127.0.0.1', 11434);
        $models  = $running ? $this->listOllamaModels() : [];

        return [
            'installed' => true,
            'path'      => $exe,
            'running'   => $running,
            'models'    => $models,
        ];
    }

    /**
     * Procura uma instalacao do ComfyUI (pasta com main.py) em locais comuns.
     */
    public function comfyui(): array
    {
        $candidates = [
            // Componentes pre-montados do instalador offline ({InstallDir}\components\ComfyUI)
            $this->componentsPath('ComfyUI'),
            $this->expandEnv('%USERPROFILE%\\ComfyUI'),
            $this->expandEnv('%USERPROFILE%\\ComfyUI_windows_portable\\ComfyUI'),
            $this->expandEnv('%USERPROFILE%\\Documents\\ComfyUI'),
            $this->expandEnv('C:\\ComfyUI'),
            $this->expandEnv('C:\\AI\\ComfyUI'),
            $this->expandEnv('C:\\PycharmProjects\\ComfyUI'),
            $this->expandEnv('D:\\ComfyUI'),
        ];

        $found = $this->findDirContaining(array_filter($candidates), 'main.py');

        return [
            'installed' => $found !== null,
            'path'      => $found,
            'running'   => $this->isPortOpen('127.0.0.1', 8188),
            'venv'      => $found ? $this->findVenvPython($found) : null,
        ];
    }

    /**
     * Procura ACE-Step (pasta com app.py ou gradio_app.py).
     */
    public function acestep(): array
    {
        $candidates = [
            // Componentes pre-montados do instalador offline
            $this->componentsPath('ACE-Step'),
            $this->expandEnv('%USERPROFILE%\\ACE-Step'),
            $this->expandEnv('%USERPROFILE%\\Documents\\ACE-Step'),
            $this->expandEnv('C:\\ACE-Step'),
            $this->expandEnv('C:\\AI\\ACE-Step'),
            $this->expandEnv('C:\\PycharmProjects\\ACE-Step'),
            $this->expandEnv('D:\\ACE-Step'),
        ];

        $found = null;
        foreach (array_filter($candidates) as $dir) {
            if (is_dir($dir) && (is_file("$dir\\app.py") || is_file("$dir\\gradio_app.py"))) {
                $found = $dir;
                break;
            }
        }

        return [
            'installed' => $found !== null,
            'path'      => $found,
            'running'   => $this->isPortOpen('127.0.0.1', 7860),
            'venv'      => $found ? $this->findVenvPython($found) : null,
        ];
    }

    /**
     * FFmpeg disponivel? Necessario pro MoviePy montar o video final.
     */
    public function ffmpeg(): array
    {
        $exe = $this->findExecutable('ffmpeg.exe', [
            $this->expandEnv('%LocalAppData%\\Microsoft\\WinGet\\Packages\\Gyan.FFmpeg_*\\ffmpeg.exe'),
        ]);
        return ['installed' => $exe !== null, 'path' => $exe];
    }

    /**
     * Python instalado no sistema (qualquer versao 3.10+).
     */
    public function python(): array
    {
        $exe = $this->findExecutable('python.exe', []);
        if (!$exe) {
            return ['installed' => false, 'path' => null, 'version' => null];
        }
        $version = $this->captureStdout($exe, ['--version']);
        return [
            'installed' => true,
            'path'      => $exe,
            'version'   => trim($version),
        ];
    }

    /**
     * Snapshot completo — usado pelo wizard /setup.
     */
    public function snapshot(): array
    {
        return [
            'ollama'  => $this->ollama(),
            'comfyui' => $this->comfyui(),
            'acestep' => $this->acestep(),
            'ffmpeg'  => $this->ffmpeg(),
            'python'  => $this->python(),
        ];
    }

    /**
     * Escaneia uma pasta base específica (ex: {app}\components do instalador offline)
     * buscando ComfyUI, ACE-Step e o projeto NewVideoMaker pré-montados.
     *
     * Diferente dos métodos comfyui()/acestep() que varrem locais comuns do sistema,
     * este olha SÓ dentro de $baseDir — usado pelo comando setup:autoconfigure
     * para configurar tudo a partir do que veio no payload do instalador.
     *
     * @return array{
     *   comfyui: array{found:bool,path:?string,python:?string},
     *   acestep: array{found:bool,path:?string,python:?string},
     *   pipeline: array{found:bool,dir:?string,script:?string,python:?string,output:?string},
     *   ollama_installer: ?string
     * }
     */
    public function scanComponentsDir(string $baseDir): array
    {
        $baseDir = rtrim($baseDir, '\\/ ');

        // ComfyUI: pasta com main.py. Python: python_embeded (portable) ou venv.
        $comfyDir = null;
        foreach ([$baseDir.'\\ComfyUI', $baseDir.'\\comfyui'] as $c) {
            if (is_dir($c) && is_file("$c\\main.py")) { $comfyDir = $c; break; }
        }
        $comfyPython = null;
        if ($comfyDir !== null) {
            foreach ([
                $comfyDir.'\\python_embeded\\python.exe',  // ComfyUI portable
                $comfyDir.'\\.venv\\Scripts\\python.exe',
                $comfyDir.'\\venv\\Scripts\\python.exe',
            ] as $p) {
                if (is_file($p)) { $comfyPython = $p; break; }
            }
        }

        // ACE-Step: pasta com app.py ou gradio_app.py.
        $aceDir = null;
        foreach ([$baseDir.'\\ACE-Step', $baseDir.'\\ace-step', $baseDir.'\\ACE-Step-main'] as $c) {
            if (is_dir($c) && (is_file("$c\\app.py") || is_file("$c\\gradio_app.py"))) { $aceDir = $c; break; }
        }
        $acePython = $aceDir ? $this->findVenvPython($aceDir) : null;

        // Pipeline NewVideoMaker: pasta com pipeline.py.
        $pipelineDir = null;
        foreach ([
            $baseDir.'\\NewVideoMaker',
            $baseDir.'\\newvideomaker',
            $baseDir.'\\NewVideoMaker-main',
            $baseDir.'\\pipeline',
        ] as $c) {
            if (is_dir($c) && is_file("$c\\pipeline.py")) { $pipelineDir = $c; break; }
        }
        $pipelinePython = $pipelineDir ? $this->findVenvPython($pipelineDir) : null;
        $pipelineOutput = null;
        if ($pipelineDir !== null) {
            $out = $pipelineDir.'\\output';
            $pipelineOutput = is_dir($out) ? $out : $out; // mesmo se não existe ainda, é o destino
        }

        // Instalador do Ollama no payload
        $ollamaInstaller = null;
        foreach ([
            $baseDir.'\\ollama\\OllamaSetup.exe',
            $baseDir.'\\Ollama\\OllamaSetup.exe',
            $baseDir.'\\OllamaSetup.exe',
        ] as $p) {
            if (is_file($p)) { $ollamaInstaller = $p; break; }
        }

        return [
            'comfyui'  => ['found' => $comfyDir !== null,  'path' => $comfyDir,  'python' => $comfyPython],
            'acestep'  => ['found' => $aceDir !== null,    'path' => $aceDir,    'python' => $acePython],
            'pipeline' => [
                'found'  => $pipelineDir !== null,
                'dir'    => $pipelineDir,
                'script' => $pipelineDir ? $pipelineDir.'\\pipeline.py' : null,
                'python' => $pipelinePython,
                'output' => $pipelineOutput,
            ],
            'ollama_installer' => $ollamaInstaller,
        ];
    }

    // ---------- helpers ----------

    private function findExecutable(string $name, array $extraPaths): ?string
    {
        // 1) PATH do sistema
        $paths = array_filter(explode(PATH_SEPARATOR, (string) getenv('PATH')));
        foreach ($paths as $dir) {
            $full = rtrim($dir, '\\/').DIRECTORY_SEPARATOR.$name;
            if (is_file($full)) {
                return $full;
            }
        }
        // 2) locais conhecidos
        foreach ($extraPaths as $p) {
            // suporta wildcard simples (Gyan.FFmpeg_*) via glob
            if (str_contains($p, '*')) {
                $matches = glob($p) ?: [];
                if (!empty($matches)) {
                    return $matches[0];
                }
            } elseif (is_file($p)) {
                return $p;
            }
        }
        return null;
    }

    private function findDirContaining(array $dirs, string $needle): ?string
    {
        foreach ($dirs as $d) {
            if (is_dir($d) && is_file("$d\\$needle")) {
                return $d;
            }
        }
        return null;
    }

    /**
     * Tenta achar o python.exe do venv dentro de um projeto.
     */
    private function findVenvPython(string $projectDir): ?string
    {
        foreach (['.venv\\Scripts\\python.exe', 'venv\\Scripts\\python.exe', 'env\\Scripts\\python.exe'] as $rel) {
            $p = "$projectDir\\$rel";
            if (is_file($p)) {
                return $p;
            }
        }
        return null;
    }

    private function expandEnv(string $path): string
    {
        return preg_replace_callback('/%([^%]+)%/', function ($m) {
            return getenv($m[1]) ?: $m[0];
        }, $path);
    }

    /**
     * Caminho de um componente dentro de {InstallDir}\components.
     * No app instalado, base_path() = {InstallDir}\app, então components é irmão.
     * Retorna null se a pasta components nem existe (instalação só-web / dev).
     */
    private function componentsPath(string $sub): ?string
    {
        $components = dirname(base_path()).DIRECTORY_SEPARATOR.'components';
        if (!is_dir($components)) {
            return null;
        }
        return $components.DIRECTORY_SEPARATOR.$sub;
    }

    private function isPortOpen(string $host, int $port, float $timeoutSec = 0.5): bool
    {
        $sock = @fsockopen($host, $port, $errno, $errstr, $timeoutSec);
        if ($sock) { fclose($sock); return true; }
        return false;
    }

    /**
     * Lista modelos do Ollama (chama HTTP API).
     */
    private function listOllamaModels(): array
    {
        $ctx = stream_context_create(['http' => ['timeout' => 2, 'ignore_errors' => true]]);
        $body = @file_get_contents('http://127.0.0.1:11434/api/tags', false, $ctx);
        if ($body === false) { return []; }
        $data = json_decode($body, true);
        if (!is_array($data) || !isset($data['models'])) { return []; }
        return array_map(fn($m) => $m['name'] ?? 'unknown', $data['models']);
    }

    private function captureStdout(string $exe, array $args, int $timeoutSec = 3): string
    {
        // Usa proc_open para nao depender de shell
        $cmd = array_merge([$exe], $args);
        $desc = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $proc = @proc_open($cmd, $desc, $pipes);
        if (!is_resource($proc)) { return ''; }
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $out = '';
        $deadline = microtime(true) + $timeoutSec;
        while (microtime(true) < $deadline) {
            $out .= stream_get_contents($pipes[1]) ?: '';
            $out .= stream_get_contents($pipes[2]) ?: '';
            $st = proc_get_status($proc);
            if (!$st['running']) { break; }
            usleep(50000);
        }
        foreach ($pipes as $p) { @fclose($p); }
        @proc_terminate($proc);
        @proc_close($proc);
        return $out;
    }
}

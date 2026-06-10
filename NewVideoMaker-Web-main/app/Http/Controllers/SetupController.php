<?php

namespace App\Http\Controllers;

use App\Jobs\InstalarOllama;
use App\Jobs\PullModeloOllama;
use App\Models\SetupTask;
use App\Services\AppConfig;
use App\Services\ServiceDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SetupController extends Controller
{
    public function __construct(
        private readonly ServiceDetector $detector,
        private readonly AppConfig $appConfig,
    ) {
    }

    public function index(): \Illuminate\View\View
    {
        $snapshot = $this->detector->snapshot();

        // Pre-popula AppConfig com paths detectados quando o cliente ainda nao configurou
        // (faz a UI ja mostrar valores razoaveis sem ele clicar em nada)
        $this->prefillIfEmpty($snapshot);

        $tasks = SetupTask::orderByDesc('id')->take(10)->get();

        return view('setup.wizard', [
            'snapshot' => $snapshot,
            'config'   => [
                'youtube_ok'    => !empty($this->appConfig->get('youtube.client_id'))
                                   && $this->appConfig->isSet('youtube.client_secret'),
                'python_path'   => $this->appConfig->get('videogen.python_path'),
                'pipeline_path' => $this->appConfig->get('videogen.pipeline_path'),
                'output_dir'    => $this->appConfig->get('videogen.output_dir'),
            ],
            'tasks'    => $tasks,
            'completed'=> $this->appConfig->get('setup.completed') === '1',
            'source'   => $this->appConfig->get('setup.source'), // 'installer-payload' quando veio pré-montado
        ]);
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'snapshot' => $this->detector->snapshot(),
            'tasks'    => SetupTask::orderByDesc('id')->take(10)->get(),
        ]);
    }

    public function installOllama(): JsonResponse
    {
        // Evita duplicar: se ja tem um job rodando, retorna o existente
        $running = SetupTask::where('kind','install_ollama')
                            ->whereIn('status', ['pending','running'])->first();
        if ($running) {
            return response()->json(['task' => $running, 'duplicate' => true]);
        }

        $task = SetupTask::create([
            'kind'  => 'install_ollama',
            'label' => 'Baixar e instalar Ollama',
        ]);
        InstalarOllama::dispatch($task->id)->onQueue('youtube'); // mesma fila de utilitarios
        return response()->json(['task' => $task]);
    }

    public function pullModel(Request $request): JsonResponse
    {
        $data = $request->validate([
            'model' => ['required','string','max:80','regex:/^[a-zA-Z0-9_:\-\.\/]+$/'],
        ]);

        $running = SetupTask::where('kind','pull_model')
                            ->where('label','LIKE',"%{$data['model']}%")
                            ->whereIn('status', ['pending','running'])->first();
        if ($running) {
            return response()->json(['task' => $running, 'duplicate' => true]);
        }

        $task = SetupTask::create([
            'kind'  => 'pull_model',
            'label' => "Baixar modelo Ollama: {$data['model']}",
        ]);
        PullModeloOllama::dispatch($task->id, $data['model'])->onQueue('youtube');
        return response()->json(['task' => $task]);
    }

    /**
     * Salva path manualmente (ex: cliente apontou onde esta o ComfyUI).
     * Valida que pasta contem o arquivo-chave (main.py / app.py).
     */
    public function savePath(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service' => ['required', Rule::in(['comfyui','acestep','pipeline'])],
            'path'    => ['required','string','max:500'],
        ]);

        $path = rtrim($data['path'], '\\/ ');
        $errors = [];

        switch ($data['service']) {
            case 'comfyui':
                if (!is_dir($path) || !is_file("$path\\main.py")) {
                    $errors[] = 'Pasta nao contem main.py (nao parece ser uma instalacao ComfyUI).';
                }
                break;
            case 'acestep':
                if (!is_dir($path) || (!is_file("$path\\app.py") && !is_file("$path\\gradio_app.py"))) {
                    $errors[] = 'Pasta nao contem app.py ou gradio_app.py.';
                }
                break;
            case 'pipeline':
                if (!is_file($path) || !str_ends_with(strtolower($path), 'pipeline.py')) {
                    $errors[] = 'Caminho nao aponta para um pipeline.py existente.';
                }
                break;
        }

        if (!empty($errors)) {
            return response()->json(['ok' => false, 'errors' => $errors], 422);
        }

        // Persistir em AppConfig. ComfyUI/ACE-Step nao tem chave propria ainda --
        // armazenamos pra exibir status; o launcher tem sua propria config separada.
        $this->appConfig->set("services.$data[service].path", $path);

        // Se for pipeline.py, ja preenche videogen.pipeline_path (alinha com /config)
        if ($data['service'] === 'pipeline') {
            $this->appConfig->set('videogen.pipeline_path', $path);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Marca o setup como concluido (cliente clicou em "Comecar a usar" no fim do wizard).
     */
    public function complete(): RedirectResponse
    {
        $this->appConfig->set('setup.completed', '1');
        return redirect()->route('dashboard')->with('success', 'Tudo pronto! Bom uso.');
    }

    /**
     * Permite pular o wizard mesmo com pendencias.
     */
    public function skip(): RedirectResponse
    {
        $this->appConfig->set('setup.skipped', '1');
        return redirect()->route('dashboard');
    }

    // ---------- helpers ----------

    private function prefillIfEmpty(array $snapshot): void
    {
        // Pipeline Python detectado em ACE-Step ou ComfyUI nao nos serve --
        // pipeline.py e parte do projeto NewVideoMaker (separado).
        // Mas se ACE-Step tem venv com python, podemos sugerir python_path se vazio.
        if (empty($this->appConfig->get('videogen.python_path')) && !empty($snapshot['acestep']['venv'])) {
            $this->appConfig->set('videogen.python_path', $snapshot['acestep']['venv']);
        }
        // ComfyUI tem main.py mas nao e o pipeline.py do nosso projeto -- nao prefillamos pipeline_path.
    }
}

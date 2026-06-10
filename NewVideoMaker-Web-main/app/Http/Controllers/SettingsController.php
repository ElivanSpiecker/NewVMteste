<?php

namespace App\Http\Controllers;

use App\Services\AppConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        private readonly AppConfig $appConfig,
        private readonly HealthController $health,
    ) {
    }

    public function index(): \Illuminate\View\View
    {
        return view('pages.config', [
            'youtube' => [
                'client_id'     => $this->appConfig->get('youtube.client_id'),
                // valor secreto: nunca devolvemos pra UI; só sinalizamos se está preenchido
                'client_secret_set' => $this->appConfig->isSet('youtube.client_secret'),
                'redirect_uri'  => $this->appConfig->get('youtube.redirect_uri') ?: rtrim((string) config('app.url'), '/').'/shorts/youtube/callback',
            ],
            'videogen' => [
                'python_path'   => $this->appConfig->get('videogen.python_path'),
                'pipeline_path' => $this->appConfig->get('videogen.pipeline_path'),
                'output_dir'    => $this->appConfig->get('videogen.output_dir'),
            ],
            'services' => $this->health->check(),
            'pipeline_status' => $this->health->checkPipelinePaths(),
        ]);
    }

    public function saveYoutube(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'     => ['nullable', 'string', 'max:200'],
            'client_secret' => ['nullable', 'string', 'max:200'],
            'redirect_uri'  => ['nullable', 'url', 'max:500'],
        ]);

        $this->appConfig->setMany([
            'youtube.client_id'     => $data['client_id']     ?? null,
            'youtube.client_secret' => $data['client_secret'] ?? null,
            'youtube.redirect_uri'  => $data['redirect_uri']  ?? null,
        ], skipNull: true);

        return redirect()->route('config')->with('success', 'Credenciais do YouTube salvas.');
    }

    public function savePipeline(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'python_path'   => ['nullable', 'string', 'max:500'],
            'pipeline_path' => ['nullable', 'string', 'max:500'],
            'output_dir'    => ['nullable', 'string', 'max:500'],
        ]);

        // Validações best-effort de existência (mensagens, não bloqueio — cliente pode estar
        // configurando antes de instalar o pipeline)
        $warnings = [];
        if (!empty($data['python_path']) && !is_file($data['python_path'])) {
            $warnings[] = 'Python: arquivo informado não foi encontrado no disco.';
        }
        if (!empty($data['pipeline_path']) && !is_file($data['pipeline_path'])) {
            $warnings[] = 'pipeline.py: arquivo informado não foi encontrado no disco.';
        }
        if (!empty($data['output_dir']) && !is_dir($data['output_dir'])) {
            $warnings[] = 'Pasta de saída: diretório informado não existe.';
        }

        $this->appConfig->setMany([
            'videogen.python_path'   => $data['python_path']   ?? null,
            'videogen.pipeline_path' => $data['pipeline_path'] ?? null,
            'videogen.output_dir'    => $data['output_dir']    ?? null,
        ], skipNull: false);

        $message = 'Caminhos do pipeline salvos.';
        if (!empty($warnings)) {
            $message .= ' Avisos: '.implode(' / ', $warnings);
        }

        return redirect()->route('config')->with('success', $message);
    }
}
